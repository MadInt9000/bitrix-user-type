<?php

namespace WebArch\BitrixUserPropertyType;


use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Sale\Internals\DiscountTable;
use WebArch\BitrixUserPropertyType\Abstraction\DbColumnType\IntegerColTypeTrait;
use WebArch\BitrixUserPropertyType\Abstraction\UserTypeBase;
use WebArch\BitrixUserPropertyType\Abstraction\UserTypeInterface;

/**
 * Class BasketRuleType
 *
 * @package WebArch\BitrixUserPropertyType
 */
class BasketRuleType extends UserTypeBase implements UserTypeInterface
{
    use IntegerColTypeTrait;

    const USER_TYPE = 'basket_rule';
    const SETTING_ID = 'BASKET_RULE_ID';

    /**
     * @throws LoaderException
     */
    public static function init()
    {
        if (Loader::includeModule('sale')) {
            parent::init();
        }
    }

    /**
     * @inheritdoc
     */
    public static function getBaseType()
    {
        return static::BASE_TYPE_INT;
    }

    /**
     * @inheritdoc
     */
    public static function getDescription()
    {
        return 'Правило работы с корзиной (sale_discount)';
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     * @return string
     */
    public static function getUserTypeId()
    {
        return static::USER_TYPE;
    }

    /**
     * @inheritdoc
     */
    public static function getSettingsHTML($userField, $htmlControl, $isVarsFromForm)
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public static function getAdminListViewHtml($userField, $htmlControl)
    {
        return $userField['SETTINGS'][static::SETTING_ID];
    }


    /**
     * @inheritdoc
     */
    public static function prepareSettings($userField)
    {
        return [
            static::SETTING_ID => (int)$userField['SETTINGS'][static::SETTING_ID],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getEditFormHTML($userField, $htmlControl)
    {
        try {
            $basketRuleOptions = static::getBasketRuleOptionList($userField['SETTINGS'][static::SETTING_ID]);
        } catch (ArgumentException $e) {
        }

        $basketRuleIdSetting = static::SETTING_ID;

        return <<<END
        <tr>
            <td>
                Правило работы с корзиной:
            </td>
            <td>
                <select name="{$htmlControl['NAME']}[{$basketRuleIdSetting}]" >{$basketRuleOptions}</select>
            </td>
        </tr>
END;
    }

    /**
     * @param $currentValue
     *
     * @return string
     * @throws ArgumentException
     */
    private static function getBasketRuleOptionList($currentValue)
    {
        $html = '<option value="0" >(не выбран)</option>';

        foreach (self::getBasketRules() as $id => $name) {
            /** @noinspection HtmlUnknownAttribute */
            $html .= sprintf(
                '<option value="%d" %s >%s</option>',
                $id,
                $currentValue == $id ? ' selected="selected" ' : '',
                $name
            );
        }

        return $html;
    }

    /**
     * @return array
     * @throws ArgumentException
     */
    private static function getBasketRules()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $db = DiscountTable::getList(['select' => ['*']]);

        $result = [];
        /** @noinspection PhpUndefinedMethodInspection */
        foreach ($db->fetchAll() as $row) {
            $result[$row['ID']] = $row['NAME'];
        }

        return $result;
    }
}
