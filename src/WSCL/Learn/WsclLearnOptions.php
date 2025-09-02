<?php
declare(strict_types=1);
namespace WSCL\Learn;

use RCS\WP\PluginOptions;

class WsclLearnOptions extends PluginOptions implements WsclLearnOptionsInterface
{
    public const OPTION_NAME = 'wscl_learn_site_options';

    public const SITE_EMAIL_NAME_KEY = 'siteEmailName';
    public const SITE_EMAIL_ADDRESS_KEY = 'siteEmailAddress';

    public const MSG_FROM_NAME_ID = 'msgFromNameId';
    public const MSG_FROM_ADDRESS_ID = 'msgFromAddressId';
    public const MSG_SUBJECT_ID = 'msgSubjectId';
    public const MSG_BODY_ID = 'msgBodyId';

    /**
     *
     * {@inheritDoc}
     * @see \RCS\WP\PluginOptions::getOptionName()
     */
    public function getOptionName(): string
    {
        return self::OPTION_NAME;
    }

    /**
     *
     * {@inheritDoc}
     * @see \RCS\WP\PluginOptions::getOptionKeys()
     */
    protected function getOptionKeys(): array
    {
        return [
            self::SITE_EMAIL_NAME_KEY,
            self::SITE_EMAIL_ADDRESS_KEY,
            self::MSG_FROM_NAME_ID,
            self::MSG_FROM_ADDRESS_ID,
            self::MSG_SUBJECT_ID,
            self::MSG_BODY_ID
            ];
    }

    /**
     *
     * {@inheritDoc}
     * @see \RCS\WP\PluginOptions::initializeInstance()
     */
    protected function initializeInstance(): void
    {
        parent::initializeInstance();

        if (empty($this->getSiteEmailName())) {
            $this->setValue(self::SITE_EMAIL_NAME_KEY, 'WSCL Learning');
            $this->setValue(self::SITE_EMAIL_ADDRESS_KEY, 'info@washingtonleague.org');
        }
    }


    /*
     * Convienence functions
     */
    public function setMsgFromName(string $name): void
    {
        $this->setValue(self::MSG_FROM_NAME_ID, $name);
    }

    public function getMsgFromName(): string
    {
        return $this->getValue(self::MSG_FROM_NAME_ID);
    }

    public function setMsgFromAddress(string $address): void
    {
        $this->setValue(self::MSG_FROM_ADDRESS_ID, $address);
    }

    public function getMsgFromAddress(): string
    {
        return $this->getValue(self::MSG_FROM_ADDRESS_ID);
    }

    public function setMsgSubject(string $subject): void
    {
        $this->setValue(self::MSG_SUBJECT_ID, $subject);
    }

    public function getMsgSubject(): string
    {
        return $this->getValue(self::MSG_SUBJECT_ID);
    }

    public function setMsgBody(string $body): void
    {
        $this->setValue(self::MSG_BODY_ID, $body);
    }

    public function getMsgBody(): string
    {
        return $this->getValue(self::MSG_BODY_ID);
    }

    public function getSiteEmailName(): string
    {
        return $this->getValue(self::SITE_EMAIL_NAME_KEY);
    }

    public function getSiteEmailAddress(): string
    {
        return $this->getValue(self::SITE_EMAIL_ADDRESS_KEY);
    }
}
