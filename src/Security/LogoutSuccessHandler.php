<?php

namespace App\Security;

use App\Model\ConfigModel;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class LogoutSuccessHandler implements LogoutSuccessHandlerInterface
{
    /**
     * @var ConfigModel
     */
    private $configModel;

    /**
     * LogoutSuccessHandler constructor.
     *
     * @param ConfigModel $configModel
     */
    public function __construct(ConfigModel $configModel)
    {
        $this->configModel = $configModel;
    }

    /**
     * {@inheritdoc}
     */
    public function onLogoutSuccess(Request $request)
    {
        return new RedirectResponse($this->configModel->getLogoutRedirectUrl());
    }
}
