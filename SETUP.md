NFQ Akademija Gallery
----------------------
Reikalavimai: Naudotas jūsų vagrant failiukas.
PHP Version 5.5.4
MYSQL: Server version: 5.5.31-0+wheezy1

1) vagrant up - vagrant failiukas jūsų, todėl parašius vagrant up turėtų puslapis būti pasiekiamas
http://nfqakademija.dev/ Aš dar turėjau mažą problemą su virtual host. Reikėjo dar /etc/apache2
apache2.conf įdėti tokią eilutę # Include the virtual host configurations: Include sites-enabled/1-nfqakademija.dev.conf
nes inludindavo atrodo kitokį failą.

2) doctrine.local.php surašom konfiguraciją prisijungimui prie db ir sukuraim duomenų bazę

3) Sugeneruojam entities
`bin/doctrine-module orm:generate-entities --generate-methods=true --regenerate-entities=true module/Application/src/`

4) Sukuriam lenteles
`bin/doctrine-module orm:schema-tool:create`

5) Naršyklėje nueiname tokiu adresu (http://nfqakademija.dev/createAdmin) ir automatiškai sukuriamas default admin iš user.global.php config'o (login: admin@admin.lt pw: Admin123)

6) Norint startuoti testus reikia būti /tests/functional direktorijoje ir parašyti ../../bin/behat

Komandos:
Generate entities: `bin/doctrine-module orm:generate-entities --generate-methods=true --regenerate-entities=true module/Application/src/`
Update database schema: `bin/doctrine-module orm:schema-tool:update --force`
Create database schema: `bin/doctrine-module orm:schema-tool:create`
Drop database schema: `bin/doctrine-module orm:schema-tool:drop --force`