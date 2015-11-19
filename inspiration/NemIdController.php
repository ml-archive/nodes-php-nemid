<?php

use App\Http\Controllers\Controller;
use Nodes\NemId\Login\Errors\ErrorHandler;
use Nodes\NemId\Login\Login as NemIdLogin;
use Nodes\NemId\Model\Mode;
use Nodes\NemId\UserCertificateCheck\TrustedRootDigests;
use Nodes\NemId\UserCertificateCheck\UserCertificateCheck;

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

        if(!UserCertificateCheck::isXml($response)) {
            $error = ErrorHandler::getByCode($response);

            // Redirect with error $error->toJson()
        }

        // Check certificate
        $userCertificate = new UserCertificateCheck();
        $certificate = $userCertificate->checkAndReturnCertificate($response, TrustedRootDigests::get(), false);

        // Redirect with login info $certificate->getSubject()->toJson();
    }
}