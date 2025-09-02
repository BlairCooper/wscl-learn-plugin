<?php
declare(strict_types = 1);
namespace WSCL\Learn;

use RCS\WP\PluginOptionsInterface;

interface WsclLearnOptionsInterface extends PluginOptionsInterface
{
    public function setMsgFromName(string $name): void;
    public function getMsgFromName(): string;

    public function setMsgFromAddress(string $address): void;
    public function getMsgFromAddress(): string;

    public function setMsgSubject(string $subject): void;
    public function getMsgSubject(): string;

    public function setMsgBody(string $body): void;
    public function getMsgBody(): string;

    public function getSiteEmailName(): string;

    public function getSiteEmailAddress(): string;
}
