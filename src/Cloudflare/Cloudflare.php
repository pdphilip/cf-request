<?php

namespace PDPhilip\CfRequest\Cloudflare;

class Cloudflare
{
    public static function setCfHeaders(): array
    {
        return (new TransformRules)->setLaravelHeaders();
    }

    public static function checkCfHeaders(): bool
    {
        return (new TransformRules)->findLaravelRule() !== null;
    }

    public static function getCfHeaders(): array
    {
        return (new TransformRules)->getResponseHeadersRuleset()->asArray();
    }

    public static function deleteRuleset(string $id): bool
    {
        return (new TransformRules)->deleteRuleSet($id)->isSuccessFul();
    }

    public static function getRulesetId(): ?string
    {
        return (new TransformRules)->getRulesetId();
    }
}
