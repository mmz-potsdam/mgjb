Create database

    bin/console doctrine:database:create

Create schema

    bin/console doctrine:schema:create


Make var writable

    sudo setfacl -R -m u:www-data:rwX -m u:"$(whoami)":rwX ./var
    sudo setfacl -dR -m u:www-data:rwX -m u:"$(whoami)":rwX ./var
