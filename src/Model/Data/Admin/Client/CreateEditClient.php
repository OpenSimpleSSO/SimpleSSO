<?php

namespace App\Model\Data\Admin\Client;

use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CreateEditClient
{
    /**
     * @var string
     *
     * @Constraints\NotBlank(message="client.title.notBlank")
     * @Constraints\Length(
     *     min=2, minMessage="client.title.minLength",
     *     max=80, maxMessage="client.title.maxLength",
     * )
     */
    public $title;

    /**
     * @var string
     *
     * @Constraints\NotBlank(message="client.publicKey.notBlank")
     */
    public $publicKey;

    /**
     * @var string
     *
     * @Constraints\NotBlank(message="client.redirectUrl.notBlank")
     * @Constraints\Url(message="client.redirectUrl.url")
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
                ->buildViolation('client.publicKey.invalidKey')
                ->atPath('publicKey')
                ->addViolation();
        }
    }
}
