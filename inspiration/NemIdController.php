<?php

use App\Http\Controllers\Controller;
use Nodes\NemId\Login\Errors\ErrorHandler;
use Nodes\NemId\Login\Login as NemIdLogin;
use Nodes\NemId\Core\Mode;
use Nodes\NemId\Login\CertificationCheck\CertificationCheck;

class NemIdController extends Controller
{

    /**
     * Login view for nemid
     *
     * @author Casper Rasmussen <cr@nodes.dk>
     * @return \Illuminate\View\View
     */
    public function view()
    {
        $nemIdLogin = new NemIdLogin(new Mode(NemIdTest::isTest()));

        return view('applet', compact('nemIdLogin'));
    }


    /**
     * Callback after login
     *
     * @author Casper Rasmussen <cr@nodes.dk>
     */
    public function callback()
    {
        // Decode response
        $response = base64_decode(\Input::get('response'));

        if(!CertificationCheck::isXml($response)) {
            $error = ErrorHandler::getByCode($response);

            // Redirect with error $error->toJson()
        }

        // Check certificate
        try {
            $userCertificate = new CertificationCheck();
            $certificate = $userCertificate->checkAndReturnCertificate($response);
        } catch (\Exception $e) {
            // Error with validation of certificate chain or signature

        }

        // Successfully
        // Redirect with login info $certificate->getSubject()->toJson();
    }
}