<?php

namespace App\Model\Data\Admin\Client;

use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CreateEditClient
{
    /**
     * @var string
     *
     * @Constraints\NotBlank(message="admin.client.createEditClient.title.notBlank")
     * @Constraints\Length(
     *     min=2, minMessage="admin.client.createEditClient.title.minLength",
     *     max=80, maxMessage="admin.client.createEditClient.title.maxLength",
     * )
     */
    public $title;

    /**
     * @var string
     *
     * @Constraints\NotBlank(message="admin.client.createEditClient.publicKey.notBlank")
     */
    public $publicKey;

    /**
     * @var string
     *
     * @Constraints\NotBlank(message="admin.client.createEditClient.redirectUrl.notBlank")
     * @Constraints\Url(message="admin.client.createEditClient.redirectUrl.url")
     */
    public $redirectUrl;

    /**
     * @Constraints\Callback()
     *
     * @param ExecutionContextInterface $context
     */
    public function validatePublicKey(ExecutionContextInterface $context): void
    {
        if (openssl_pkey_get_public($this->publicKey) === false) {
            $context
                ->buildViolation('admin.client.createEditClient.publicKey.invalidKey')
                ->atPath('publicKey')
                ->addViolation();
        }
    }
}
