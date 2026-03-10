1. Definition: What Is Authentication in Symfony?

In Symfony, authentication is the mechanism that:

Identifies the user (email, username, token, etc.).

Validates their credentials (password, API key, OAuth token, etc.).

Provides a User object to the rest of the application once the user is authenticated.

Symfony uses the Security component, which includes:

A User provider (loads user data)

One or more authenticators (controls how login is performed)

A firewall (defines which parts of the site require authentication)

A security token stored in the session after successful login

2. How Authentication Works in Symfony (Step-by-Step)

Below is the full authentication flow in Symfony for a standard login form, API login, LDAP login, etc.

Step 1 — The User Makes a Request

The user accesses a protected page or sends login credentials.

The request enters the Symfony application and hits the security firewall, configured in security.yaml.

Example:
```
security:
  firewalls:
    main:
      pattern: ^/
      custom_authenticator: App\Security\LoginFormAuthenticator
      entry_point: App\Security\LoginFormAuthenticator
      lazy: true

```
The firewall determines:

Whether authentication is required

Which authenticators to use


Step 2 — The Authenticator Tries to Authenticate the User

Symfony uses authenticators (classes implementing Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface), such as:

Login form authenticator

JSON login authenticator

HTTP Basic auth

JWT authenticator

Custom authenticator

Each authenticator does:

2.1 Extract Credentials

Example (login form):
```php
$email = $request->request->get('email');
$password = $request->request->get('password');
```

2.2 Load User

It uses the User Provider:
Example (login form):

```php
public function loadUserByIdentifier(string $identifier): User
```
Most projects use the entity:

App\Entity\User

2.3 Validate the Credentials

Credentials validation is usually handled via the PasswordHasher:
Example (login form):
```php
  $this->passwordHasher->isPasswordValid($user, $password);
```

1. Authentication Flow Diagram (Symfony Security – High-Level)

                    ┌──────────────────────────────┐
                    │      User makes a request     │
                    └──────────────┬───────────────┘
                                   │
                       Request enters Firewall
                                   │
                    ┌──────────────▼───────────────┐
                    │ Firewall checks if the route  │
                    │ requires authentication        │
                    └──────────────┬───────────────┘
                                   │
                       If authentication required
                                   │
                    ┌──────────────▼───────────────┐
                    │     Authenticator triggered    │
                    └──────────────┬───────────────┘
                                   │
                      Extract credentials (email, pwd...)
                                   │
                    ┌──────────────▼───────────────┐
                    │   User Provider loads User     │
                    │   from DB or other source      │
                    └──────────────┬───────────────┘
                                   │
                        Validate credentials
                                   │
                    ┌──────────────▼───────────────┐
                    │ Authentication Successful?    │
                    └───────┬─────────┬────────────┘
                            │         │
                          Yes       No
                            │         │
        ┌───────────────────▼─┐   ┌───▼─────────────────────┐
        │ Create Security Token│   │ onAuthenticationFailure │
        │ Save Token in Session│   │ Redirect or 401 JSON    │
        └───────────┬─────────┘   └───────────────┬────────┘
                    │                             │
            onAuthenticationSuccess                │
                    │                             │
           Redirect / Return JSON                 End

2. Fully Working Login Form Authenticator Example
This example uses LoginFormAuthenticator.php and a standard form login.

```php
src/Security/LoginFormAuthenticator.php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator
    ) {}

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($password),
            [
                new RememberMeBadge()
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, $token, string $firewallName): Response
    {
        return new Response('', Response::HTTP_FOUND, [
            'Location' => $this->urlGenerator->generate('dashboard')
        ]);
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate('app_login');
    }
}
```
3. Complete security.yaml Example (Symfony 6–7 Compatible)
security:

```php
  password_hashers:
    App\Entity\User:
      algorithm: auto

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
      provider: app_user_provider
      lazy: true
      form_login:
        login_path: app_login
        check_path: app_login
      custom_authenticator:
        - App\Security\LoginFormAuthenticator
      logout:
        path: app_logout
        target: app_login

  access_control:
    - { path: ^/admin, roles: ROLE_ADMIN }
    - { path: ^/dashboard, roles: ROLE_USER }
```

4. How to Build an OTP-Based Authenticator (High-Level Guide)

Your OTP system can be implemented as a custom authenticator.

A. OTP Flow You Should Implement

User enters their email.

Backend generates a 6-digit OTP.

OTP stored temporarily (DB or cache).

OTP sent by email or SMS.

User provides OTP in a form.

Authenticator validates the OTP.

User is logged in without a password.

B. Code Structure (Simplified)
1. Create an OTP Authenticator

src/Security/OtpAuthenticator.php

Key methods:
```php
public function authenticate(Request $request): Passport
{
    $email = $request->request->get('email');
    $otp = $request->request->get('otp');

    return new Passport(
        new UserBadge($email),
        new CustomCredentials(
            function ($receivedOtp, User $user) {
                return $this->otpService->validate($user, $receivedOtp);
            },
            $otp
        )
    );
}


5. Session Authentication vs JWT Authentication (Comparison)

| Criteria    | Session Authentication                    | JWT Authentication                                       |
| ----------- | ----------------------------------------- | -------------------------------------------------------- |
| Storage     | Token stored in **session** (server side) | Token stored **client side** (localStorage / cookies)    |
| Use Case    | Browser-based websites                    | APIs, mobile apps, SPAs                                  |
| Stateless   | No (session required)                     | Yes (no server storage needed)                           |
| Security    | Very secure; resistant to tampering       | Requires careful management of tokens                    |
| Logout      | Easy (invalidate session)                 | Hard (JWT cannot be invalidated unless using blacklists) |
| Performance | Requires DB or session storage lookups    | Very fast (no server lookup needed)                      |
| Ideal When  | Symfony full-stack app with Twig, forms   | React, Vue, mobile apps, microservices                   |
