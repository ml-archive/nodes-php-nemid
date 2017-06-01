# Using test certificates

Create a directory called `testcertificates`

Download the file `DanID Test (gyldig)` under `Virksomhedscertifikater` from `https://www.nets.eu/dk-da/kundeservice/nemid-tjenesteudbyder/NemID-tjenesteudbyderpakken/Pages/OCES-II-certifikat-eksempler.aspx`

Convert it to pem file `openssl pkcs12 -in VOCES_gyldig.p12 -out certificate.cer -nodes` use the password `Test1234`

Now open `certificate.cer` in your favorite editor

Find the line that begins with `-----BEGIN PRIVATE KEY-----` copy from here until `-----END PRIVATE KEY-----`

Save the content as `test_private.pem` in `testcertificates` directory

Find the FIRST line that begins with `-----BEGIN CERTIFICATE-----` copy from here until the FIRST `-----END CERTIFICATE-----`

Save the content as `test_public.pem` in `testcertificates` directory

Now fire up a PHP server, and run `test.php`

With PHP internal server it can be done with `php -S localhost:8000 -t .`
