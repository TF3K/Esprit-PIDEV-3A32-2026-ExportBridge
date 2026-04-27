<?php

namespace App\Tests\EventSubscriber;

use App\EventSubscriber\OAuthLoginSuccessSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class OAuthLoginSuccessSubscriberTest extends TestCase
{
    public function testRedirectsAfterGoogleOAuthLogin(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects(self::once())
            ->method('generate')
            ->with('app_redirect')
            ->willReturn('/redirect');

        $subscriber = new OAuthLoginSuccessSubscriber($urlGenerator);
        $event = $this->createMock(LoginSuccessEvent::class);
        $request = new Request();
        $request->attributes->set('_route', 'hwi_oauth_check');

        $event->method('getRequest')->willReturn($request);
        $event->expects(self::once())
            ->method('setResponse')
            ->with(self::callback(static function (RedirectResponse $response): bool {
                return $response->getTargetUrl() === '/redirect';
            }));

        $subscriber->onLoginSuccess($event);
    }

    public function testIgnoresNonOAuthRoutes(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects(self::never())->method('generate');

        $subscriber = new OAuthLoginSuccessSubscriber($urlGenerator);
        $event = $this->createMock(LoginSuccessEvent::class);
        $request = new Request();
        $request->attributes->set('_route', 'app_login');

        $event->method('getRequest')->willReturn($request);
        $event->expects(self::never())->method('setResponse');

        $subscriber->onLoginSuccess($event);
    }
}
