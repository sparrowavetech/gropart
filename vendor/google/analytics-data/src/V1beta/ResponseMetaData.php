<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/analytics/data/v1beta/data.proto

namespace Google\Analytics\Data\V1beta;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Response's metadata carrying additional information about the report content.
 *
 * Generated from protobuf message <code>google.analytics.data.v1beta.ResponseMetaData</code>
 */
class ResponseMetaData extends \Google\Protobuf\Internal\Message
{
    /**
     * If true, indicates some buckets of dimension combinations are rolled into
     * "(other)" row. This can happen for high cardinality reports.
     *
     * Generated from protobuf field <code>bool data_loss_from_other_row = 3;</code>
     */
    private $data_loss_from_other_row = false;
    /**
     * Describes the schema restrictions actively enforced in creating this
     * report. To learn more, see [Access and data-restriction
     * management](https://support.google.com/analytics/answer/10851388).
     *
     * Generated from protobuf field <code>optional .google.analytics.data.v1beta.ResponseMetaData.SchemaRestrictionResponse schema_restriction_response = 4;</code>
     */
    private $schema_restriction_response = null;
    /**
     * The currency code used in this report. Intended to be used in formatting
     * currency metrics like `purchaseRevenue` for visualization. If currency_code
     * was specified in the request, this response parameter will echo the request
     * parameter; otherwise, this response parameter is the property's current
     * currency_code.
     * Currency codes are string encodings of currency types from the ISO 4217
     * standard (https://en.wikipedia.org/wiki/ISO_4217); for example "USD",
     * "EUR", "JPY". To learn more, see
     * https://support.google.com/analytics/answer/9796179.
     *
     * Generated from protobuf field <code>optional string currency_code = 5;</code>
     */
    private $currency_code = null;
    /**
     * The property's current timezone. Intended to be used to interpret
     * time-based dimensions like `hour` and `minute`. Formatted as strings from
     * the IANA Time Zone database (https://www.iana.org/time-zones); for example
     * "America/New_York" or "Asia/Tokyo".
     *
     * Generated from protobuf field <code>optional string time_zone = 6;</code>
     */
    private $time_zone = null;
    /**
     * If empty reason is specified, the report is empty for this reason.
     *
     * Generated from protobuf field <code>optional string empty_reason = 7;</code>
     */
    private $empty_reason = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type bool $data_loss_from_other_row
     *           If true, indicates some buckets of dimension combinations are rolled into
     *           "(other)" row. This can happen for high cardinality reports.
     *     @type \Google\Analytics\Data\V1beta\ResponseMetaData\SchemaRestrictionResponse $schema_restriction_response
     *           Describes the schema restrictions actively enforced in creating this
     *           report. To learn more, see [Access and data-restriction
     *           management](https://support.google.com/analytics/answer/10851388).
     *     @type string $currency_code
     *           The currency code used in this report. Intended to be used in formatting
     *           currency metrics like `purchaseRevenue` for visualization. If currency_code
     *           was specified in the request, this response parameter will echo the request
     *           parameter; otherwise, this response parameter is the property's current
     *           currency_code.
     *           Currency codes are string encodings of currency types from the ISO 4217
     *           standard (https://en.wikipedia.org/wiki/ISO_4217); for example "USD",
     *           "EUR", "JPY". To learn more, see
     *           https://support.google.com/analytics/answer/9796179.
     *     @type string $time_zone
     *           The property's current timezone. Intended to be used to interpret
     *           time-based dimensions like `hour` and `minute`. Formatted as strings from
     *           the IANA Time Zone database (https://www.iana.org/time-zones); for example
     *           "America/New_York" or "Asia/Tokyo".
     *     @type string $empty_reason
     *           If empty reason is specified, the report is empty for this reason.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Analytics\Data\V1Beta\Data::initOnce();
        parent::__construct($data);
    }

    /**
     * If true, indicates some buckets of dimension combinations are rolled into
     * "(other)" row. This can happen for high cardinality reports.
     *
     * Generated from protobuf field <code>bool data_loss_from_other_row = 3;</code>
     * @return bool
     */
    public function getDataLossFromOtherRow()
    {
        return $this->data_loss_from_other_row;
    }

    /**
     * If true, indicates some buckets of dimension combinations are rolled into
     * "(other)" row. This can happen for high cardinality reports.
     *
     * Generated from protobuf field <code>bool data_loss_from_other_row = 3;</code>
     * @param bool $var
     * @return $this
     */
    public function setDataLossFromOtherRow($var)
    {
        GPBUtil::checkBool($var);
        $this->data_loss_from_other_row = $var;

        return $this;
    }

    /**
     * Describes the schema restrictions actively enforced in creating this
     * report. To learn more, see [Access and data-restriction
     * management](https://support.google.com/analytics/answer/10851388).
     *
     * Generated from protobuf field <code>optional .google.analytics.data.v1beta.ResponseMetaData.SchemaRestrictionResponse schema_restriction_response = 4;</code>
     * @return \Google\Analytics\Data\V1beta\ResponseMetaData\SchemaRestrictionResponse|null
     */
    public function getSchemaRestrictionResponse()
    {
        return $this->schema_restriction_response;
    }

    public function hasSchemaRestrictionResponse()
    {
        return isset($this->schema_restriction_response);
    }

    public function clearSchemaRestrictionResponse()
    {
        unset($this->schema_restriction_response);
    }

    /**
     * Describes the schema restrictions actively enforced in creating this
     * report. To learn more, see [Access and data-restriction
     * management](https://support.google.com/analytics/answer/10851388).
     *
     * Generated from protobuf field <code>optional .google.analytics.data.v1beta.ResponseMetaData.SchemaRestrictionResponse schema_restriction_response = 4;</code>
     * @param \Google\Analytics\Data\V1beta\ResponseMetaData\SchemaRestrictionResponse $var
     * @return $this
     */
    public function setSchemaRestrictionResponse($var)
    {
        GPBUtil::checkMessage($var, \Google\Analytics\Data\V1beta\ResponseMetaData\SchemaRestrictionResponse::class);
        $this->schema_restriction_response = $var;

        return $this;
    }

    /**
     * The currency code used in this report. Intended to be used in formatting
     * currency metrics like `purchaseRevenue` for visualization. If currency_code
     * was specified in the request, this response parameter will echo the request
     * parameter; otherwise, this response parameter is the property's current
     * currency_code.
     * Currency codes are string encodings of currency types from the ISO 4217
     * standard (https://en.wikipedia.org/wiki/ISO_4217); for example "USD",
     * "EUR", "JPY". To learn more, see
     * https://support.google.com/analytics/answer/9796179.
     *
     * Generated from protobuf field <code>optional string currency_code = 5;</code>
     * @return string
     */
    public function getCurrencyCode()
    {
        return isset($this->currency_code) ? $this->currency_code : '';
    }

    public function hasCurrencyCode()
    {
        return isset($this->currency_code);
    }

    public function clearCurrencyCode()
    {
        unset($this->currency_code);
    }

    /**
     * The currency code used in this report. Intended to be used in formatting
     * currency metrics like `purchaseRevenue` for visualization. If currency_code
     * was specified in the request, this response parameter will echo the request
     * parameter; otherwise, this response parameter is the property's current
     * currency_code.
     * Currency codes are string encodings of currency types from the ISO 4217
     * standard (https://en.wikipedia.org/wiki/ISO_4217); for example "USD",
     * "EUR", "JPY". To learn more, see
     * https://support.google.com/analytics/answer/9796179.
     *
     * Generated from protobuf field <code>optional string currency_code = 5;</code>
     * @param string $var
     * @return $this
     */
    public function setCurrencyCode($var)
    {
        GPBUtil::checkString($var, True);
        $this->currency_code = $var;

        return $this;
    }

    /**
     * The property's current timezone. Intended to be used to interpret
     * time-based dimensions like `hour` and `minute`. Formatted as strings from
     * the IANA Time Zone database (https://www.iana.org/time-zones); for example
     * "America/New_York" or "Asia/Tokyo".
     *
     * Generated from protobuf field <code>optional string time_zone = 6;</code>
     * @return string
     */
    public function getTimeZone()
    {
        return isset($this->time_zone) ? $this->time_zone : '';
    }

    public function hasTimeZone()
    {
        return isset($this->time_zone);
    }

    public function clearTimeZone()
    {
        unset($this->time_zone);
    }

    /**
     * The property's current timezone. Intended to be used to interpret
     * time-based dimensions like `hour` and `minute`. Formatted as strings from
     * the IANA Time Zone database (https://www.iana.org/time-zones); for example
     * "America/New_York" or "Asia/Tokyo".
     *
     * Generated from protobuf field <code>optional string time_zone = 6;</code>
     * @param string $var
     * @return $this
     */
    public function setTimeZone($var)
    {
        GPBUtil::checkString($var, True);
        $this->time_zone = $var;

        return $this;
    }

    /**
     * If empty reason is specified, the report is empty for this reason.
     *
     * Generated from protobuf field <code>optional string empty_reason = 7;</code>
     * @return string
     */
    public function getEmptyReason()
    {
        return isset($this->empty_reason) ? $this->empty_reason : '';
    }

    public function hasEmptyReason()
    {
        return isset($this->empty_reason);
    }

    public function clearEmptyReason()
    {
        unset($this->empty_reason);
    }

    /**
     * If empty reason is specified, the report is empty for this reason.
     *
     * Generated from protobuf field <code>optional string empty_reason = 7;</code>
     * @param string $var
     * @return $this
     */
    public function setEmptyReason($var)
    {
        GPBUtil::checkString($var, True);
        $this->empty_reason = $var;

        return $this;
    }

}

