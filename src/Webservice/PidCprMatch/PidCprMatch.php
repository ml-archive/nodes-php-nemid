<?php

namespace Nodes\NemId\Webservice\PidCprMatch;

use GuzzleHttp\Client;
use Nodes\NemId\Webservice\PidCprMatch\Responses\Response;
use Nodes\NemId\Webservice\Settings;

/**
 * Class PidCprMatch.
 *
 * @author  Casper Rasmussen <cr@nodes.dk>
 */
class PidCprMatch
{
    /**
     * @var \Nodes\NemId\PidCprMatch\Settings
     */
    protected $settings;

    /**
     * PidCprMatch constructor.
     *
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @param array $settings
     * @param null  $mode
     */
    public function __construct(array $settings, $mode = null)
    {
        $this->settings = new Settings($settings, $mode);
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @param $pid
     * @param $cpr
     *
     * @throws \Exception
     *
     * @return \Nodes\NemId\PidCprMatch\Responses\Response
     */
    public function pidCprRequest($pid, $cpr)
    {
        // Generate xml document
        $pidCprRequest =
            '<?xml version="1.0" encoding="iso-8859-1"?><method name="pidCprRequest" version="1.0"><request><serviceId>x</serviceId><pid>x</pid><cpr>x</cpr></request></method>';

        $document = new \DOMDocument();
        $document->loadXML($pidCprRequest);
        $xp = new \DomXPath($document);

        $pidCprRequestParams = [
            'serviceId' => $this->settings->getServiceId(),
            'pid'       => $pid,
            'cpr'       => $cpr,
        ];

        $element = $xp->query('/method/request')
            ->item(0);
        $element->setAttribute('id', uniqid());

        foreach ((array) $pidCprRequestParams as $p => $v) {
            $element = $xp->query('/method/request/'.$p)
                ->item(0);
            $newelement = $document->createTextNode($v);
            $element->replaceChild($newelement, $element->firstChild);
        }

        $pidCprRequest = $document->saveXML();

        // Check that certificate exists
        if (!file_exists($this->settings->getCertificateAndKey())) {
            throw new \Exception('Certificate was not found');
        }

        // Init guzzle client
        $client = new Client();

        try {
            // Build params
            $params = [
                'cert'            => [
                    $this->settings->getCertificateAndKey(),
                    $this->settings->getPassword(),
                ],
                'form_params'     => [
                    'PID_REQUEST' => $pidCprRequest,
                ],
                'connect_timeout' => 10,
            ];

            // Set proxy
            if ($this->settings->hasProxy()) {
                $params['proxy'] = $this->settings->getProxy();
            }

            // Execute request
            $response = $client->request('POST', $this->settings->getServer(), $params);

            // Parse status code
            $document->loadXML($response->getBody()->getContents());
            $xp = new \DomXPath($document);
            $status = intval($xp->query('/method/response/status/@statusCode')->item(0)->value);

            return new Response($status);
        } catch (\Exception $e) {
            return new Response(-1, $e);
        }
    }
}
