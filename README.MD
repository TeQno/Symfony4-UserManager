
# Symfony 4 user manager

### .env

*Mailer et base de données requises*

```
MAILER_URL=smtp://localhost:1025
DATABASE_URL=mysql://root:root@127.0.0.1:3306/db_test
```

### composer.json

```
"require": {
    "php": "^7.1.3",
    "ext-ctype": "*",
    "ext-iconv": "*",
    "doctrine/dbal": "^2.8",
    "sensio/framework-extra-bundle": "^5.2",
    "symfony/asset": "4.1.*",
    "symfony/console": "4.1.*",
    "symfony/flex": "^1.1",
    "symfony/form": "4.1.*",
    "symfony/framework-bundle": "4.1.*",
    "symfony/monolog-bundle": "^3.3",
    "symfony/orm-pack": "^1.0",
    "symfony/security": "4.1.*",
    "symfony/security-bundle": "4.1.*",
    "symfony/swiftmailer-bundle": "^3.2",
    "symfony/translation": "4.1.*",
    "symfony/twig-bundle": "4.1.*",
    "symfony/validator": "4.1.*",
    "symfony/yaml": "4.1.*"
},
"require-dev": {
    "doctrine/doctrine-fixtures-bundle": "^3.0",
    "symfony/dotenv": "4.1.*",
    "symfony/maker-bundle": "^1.9",
    "symfony/profiler-pack": "^1.0",
    "symfony/web-server-bundle": "4.1.*"
}
```

### security.yaml

```
security:
    encoders:
            App\Entity\User:
                algorithm: argon2i
        providers:
            app_user_provider:
                entity:
                    class: App\Entity\User
                    property: email
    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false
        main:
            anonymous: true
            guard:
                authenticators:
                    - App\Security\SecurityAuthenticator
            logout:
                path:   /logout
                target: /
                invalidate_session: true
    access_control:
        - { path: ^/admin, roles: ROLE_ADMIN }
        - { path: ^/, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/login, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/register, roles: IS_AUTHENTICATED_ANONYMOUSLY }
```

**Importer la base de données**
```
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```