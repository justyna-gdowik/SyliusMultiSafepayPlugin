<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusMultiSafepayPlugin\Behat\Page\Shop\Payum;

use FriendsOfBehat\PageObjectExtension\Page\Page;
use Behat\Mink\Session;
use Payum\Core\Security\TokenInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\BrowserKit\Client;

final class PaymentPage extends Page implements PaymentPageInterface
{
    /** @var RepositoryInterface */
    private $securityTokenRepository;

    /** @var Client */
    private $client;

    public function __construct(
        Session $session,
        $parameters,
        RepositoryInterface $securityTokenRepository,
        Client $client
    ) {
        parent::__construct($session, $parameters);

        $this->securityTokenRepository = $securityTokenRepository;
        $this->client = $client;
    }

    public function capture(array $parameters = []): void
    {
        $captureToken = $this->findToken();

        $url = $captureToken->getTargetUrl();

        if (count($parameters) > 0) {
            $url .= '?' . http_build_query($parameters);
        }

        $this->getDriver()->visit($url);
    }

    public function notify(array $data): void
    {
        $notifyToken = $this->findToken('notify');

        $this->client->request('GET', sprintf('%s?%s', $notifyToken->getTargetUrl(), http_build_query($data)));
    }

    protected function getUrl(array $urlParameters = []): string
    {
        return '';
    }

    private function findToken(string $type = 'capture'): TokenInterface
    {
        $tokens = [];

        /** @var TokenInterface $token */
        foreach ($this->securityTokenRepository->findAll() as $token) {
            if (strpos($token->getTargetUrl(), $type)) {
                $tokens[] = $token;
            }
        }

        if (count($tokens) > 0) {
            return end($tokens);
        }

        throw new \RuntimeException('Cannot find capture token, check if you are after proper checkout steps');
    }
}
