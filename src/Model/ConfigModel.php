<?php

namespace App\Model;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ConfigModel
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * ConfigModel constructor.
     *
     * @param array                 $config
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(array $config, UrlGeneratorInterface $urlGenerator)
    {
        $this->config = $config;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Indicate that no registration link should be displayed.
     *
     * @return bool
     */
    public function disableRegistrationLinks(): bool
    {
        return $this->config['disable_registration_route'];
    }

    /**
     * Indicate the registration actions should not be available.
     *
     * @return bool
     */
    public function disableRegistrationActions(): bool
    {
        return $this->config['disable_registration_route'] || $this->config['registration_url'] !== null;
    }

    /**
     * @return string|null
     */
    public function getRegistrationUrl(): ?string
    {
        if ($this->config['registration_url'] === null) {
            return $this->urlGenerator->generate('userManagement.register');
        }

        return $this->config['registration_url'];
    }

    /**
     * @return string
     */
    public function getLogoutRedirectUrl(): string
    {
        if ($this->config['logout_redirect_url'] === null) {
            return $this->urlGenerator->generate('userProfile.show');
        }

        return $this->config['logout_redirect_url'];
    }
}
