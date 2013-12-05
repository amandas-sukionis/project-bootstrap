NFQ Akademija Gallery
----------------------
Create default admin login:
http://nfqakademija.dev/createAdmin

Generate entities: `bin/doctrine-module orm:generate-entities --generate-methods=true --regenerate-entities=true module/Application/src/`
Update database schema: `bin/doctrine-module orm:schema-tool:update --force`
Create database schema: `bin/doctrine-module orm:schema-tool:create`
Drop database schema: `bin/doctrine-module orm:schema-tool:drop --force`

Start tests:
../../bin/behat