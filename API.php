<?php

namespace ThisData\WordPress;
use ThisData\Api\ThisData;

class API {

    static protected $thisData = null;
    static protected $eventsEndpoint = null;

    static function getThisData() {
        return static::$thisData ?: static::$thisData = ThisData::create(static::getKey());
    }

    static function getEventsEndpoint() {

        if(!static::$eventsEndpoint) {
            $thisData = static::getThisData();
            static::$eventsEndpoint = $thisData->getEventsEndpoint();
        }

        return static::$eventsEndpoint;
    }

    static function getKey() {
        return getenv(ENV_API_KEY) ?: Admin::get_setting(Admin::SETTINGS_API_KEY);
    }

    static function getJSWriteKey() {
        return getenv(ENV_JS_WRITE_KEY) ?: Admin::get_setting(Admin::SETTINGS_JS_WRITE_KEY);
    }

    static function getWebhookSignature() {
        return static::getKey();
        /*$signature = getenv(ENV_WEBHOOK_SIGNATURE) ?: Admin::get_setting(Admin::SETTINGS_WEBHOOK_SIGNATURE);
        return $signature ?: static::getKey();*/
    }

    static function getJSSignature() {
        return static::getKey();
        //return getenv(ENV_JS_SIGNATURE) ?: Admin::get_setting(Admin::SETTINGS_JS_SIGNATURE);
    }
}
