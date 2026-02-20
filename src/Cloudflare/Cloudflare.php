<?php

namespace PDPhilip\CfRequest\Cloudflare;

class Cloudflare
{
    public static function checkCfHeaders(): bool
    {
        $cf = new TransformRules;

        return $cf->verifyHeaders();
    }

    public static function setCfHeaders(): array
    {
        $cf = new TransformRules;
        $res = $cf->setLaravelHeaders();

        return [
            'success' => $res->isSuccessFul(),
            'code' => $res->code,
            'message' => $res->message,
        ];
    }

    public static function deleteRuleset($id): bool
    {
        $cf = new TransformRules;
        $res = $cf->deleteRuleSet($id);

        return $res->isSuccessFul();
    }

    public static function getRulesetId(): ?string
    {
        $cf = new TransformRules;

        return $cf->getRulesetId();
    }

    public static function getCfHeaders(): array
    {
        $cf = new TransformRules;
        $res = $cf->getResponseHeadersRuleset();

        return $res->asArray();

    }
}
