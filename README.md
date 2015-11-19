# Nem-id integration

A PHP Laravel library for using the Danish NemID for authenticating a user.
Since there is no official PHP library for Nem-id 
 
### The library supports: 
  - Preparing the parameters for the applet
  - Checking the returned signature and the certificate chain
  - Matching PID to CPR
    
This is a rewrite of an original library for an older version of the applet in java
Original library can be found: https://code.google.com/p/nemid-php/ 
    
# Installation

So you got your p12 certificate now generate pem files, use following commands: 

#####publicCertificate:
`openssl pkcs12 -in path.p12 -out certificate.pem -clcerts -nokeys`

#####privateKey & privateKeyPassword
`openssl pkcs12 -in path.p12 -clcerts -out privateKey.pem`

#####certifateAndPrivateKey & password (For PID/CPR match)
`openssl pkcs12 -in path.p12 -out certicateAndPrivateKey.pem -nocerts -nodes`     

Now you have all the certificates needed 

##### Copy the config file to htdocs and fill settings
Look in the config file for more help

#Login integration
In the inspiration folder an example of how you can setup the login flow can be found.

First prepare parameters to inject into the iframe. By creating a Login object.

`$login = new Login();

Setup a html document with the iframe url, js with param data and a form for callbacks

`$login->getIFrameUrl();`

`$login->getParams();`

The iframe will now submit the form when there is any errors or an successfully login

The submitted has a base64 encoded error string a xml document, detect type and throw error or continue.

`$response = base64_decode(\Input::get('response'));`

Now validate the certificates and excract name and PID from it by initialize a CertificationCheck object

`$userCertificate = new CertificationCheck();`

`$certificate = $userCertificate->checkAndReturnCertificate($response);`

`$certificate->getSubject()->getName();`

`$certificate->getSubject()->getPid();`

#PID/CPR match integration
Initialize a PidCprMatch object

`$pidCprMatch = new PidCprMatch();`

 Call the function "pidCprRequest" with pid and cpr params.

`$response = $pidCprMatch->pidCprRequest($pid', $cpr);`

A response object will be returned where you can get if it was an match and possible errors

`$response->didMatch();`
         
Enjoy
 


