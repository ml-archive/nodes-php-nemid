# Nem-id integration

A PHP Laravel library for using the Danish NemID for authenticating a user.
Since there is no official PHP library for Nem-id 
 
### The library supports: 
  - Preparing the parameters for the applet
  - Checking the returned signature and the certificate chain
  - Matching PID to CPR
    
### This is a rewrite of an original library for an older version of the applet in java
### Original library can be found: https://code.google.com/p/nemid-php/ 
    
# Installation

So you got your p12 certificate now generate pem files, use following commands: 

###publicCertificate:
`openssl pkcs12 -in path.p12 -out newfile.crt.pem -clcerts -nokeys`

###privateKey & privateKeyPassword
`openssl pkcs12 -in cert.p12 -clcerts -out cert.pem`

###certifateAndPrivateKey & password (For PID/CPR match)
`openssl pkcs12 -in path.p12 -out newfile.key.pem -nocerts -nodes`     

Now you have all the certificates needed 

### Copy the config file to htdocs and fill settings



