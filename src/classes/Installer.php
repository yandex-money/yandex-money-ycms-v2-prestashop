<?php
/**
 * @author YooMoney <cms@yoomoney.ru>
 * @copyright © 2020 "YooMoney", NBСO LLC
 * @license  https://yoomoney.ru/doc.xml?id=527052
 */

namespace YooMoneyModule;

/**
 * Класс хэлпер, используемый при установку и удалении модуля
 *
 * @package YooMoneyModule
 */
class Installer
{
    /**
     * @var \yoomoneymodule
     */
    private $module;

    public function __construct(\yoomoneymodule $module)
    {
        $this->module = $module;
    }

    /**
     * Добавляет в базу данных магазина таблицы модуля
     */
    public function addDatabaseTables()
    {
        $sql = array();
        $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'yoomoney_market_orders`
            (
                `id_order` int(10) NOT NULL,
                `id_market_order` varchar(100) NOT NULL,
                `currency` varchar(100) NOT NULL,
                `ptype` varchar(100) NOT NULL,
                `home` varchar(100) NOT NULL,
                `pmethod` varchar(100) NOT NULL,
                `outlet` varchar(100) NOT NULL,
                PRIMARY KEY  (`id_order`,`id_market_order`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'yoomoney_payments` (
            `order_id`          INTEGER  NOT NULL,
            `payment_id`        CHAR(36) NOT NULL,
            `status`            ENUM(\'pending\', \'waiting_for_capture\', \'succeeded\', \'canceled\') NOT NULL,
            `amount`            DECIMAL(11, 2) NOT NULL,
            `currency`          CHAR(3) NOT NULL,
            `payment_method_id` CHAR(36) NOT NULL,
            `paid`              ENUM(\'Y\', \'N\') NOT NULL,
            `created_at`        DATETIME NOT NULL,
            `captured_at`       DATETIME NOT NULL DEFAULT \'0000-00-00 00:00:00\',

            CONSTRAINT `' . _DB_PREFIX_ . 'yoomoney_payment_pk` PRIMARY KEY (`order_id`),
            CONSTRAINT `' . _DB_PREFIX_ . 'yoomoney_payment_unq_payment_id` UNIQUE (`payment_id`) 
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'yoomoney_refunds` (
            `refund_id`         CHAR(36) NOT NULL,
            `order_id`          INTEGER  NOT NULL,
            `payment_id`        CHAR(36) NOT NULL,
            `status`            ENUM(\'pending\', \'succeeded\', \'canceled\') NOT NULL,
            `amount`            DECIMAL(11, 2) NOT NULL,
            `currency`          CHAR(3) NOT NULL,
            `created_at`        DATETIME NOT NULL,
            `authorized_at`     DATETIME NOT NULL DEFAULT \'0000-00-00 00:00:00\',
            `comment`           VARCHAR(254) NOT NULL,

            CONSTRAINT `' . _DB_PREFIX_ . 'yoomoney_refunds_pk` PRIMARY KEY (`refund_id`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8 COLLATE=utf8_general_ci';

        foreach ($sql as $query) {
            $this->module->log('debug', 'Execute query: ' . $query);
            \Db::getInstance()->execute($query);
        }
    }

    /**
     * Дропает таблицы модуля при его удалении
     */
    public function removeDatabaseTables()
    {
        $sql = array(
            'DROP TABLE IF EXISTS `'._DB_PREFIX_.'yoomoney_market_orders`',
            'DROP TABLE IF EXISTS `'._DB_PREFIX_.'yoomoney_payments`',
            'DROP TABLE IF EXISTS `'._DB_PREFIX_.'yoomoney_refunds`',
        );
        foreach ($sql as $query) {
            \Db::getInstance()->execute($query);
        }
    }

    public function installTab()
    {
        $tab = new \Tab();
        $tab->active = 1;
        $tab->class_name = \YooMoneyModule::ADMIN_CONTROLLER;
        $tab->name = array();
        foreach (\Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = \YooMoneyModule::ADMIN_CONTROLLER;
        }
        $tab->id_parent = -1;
        $tab->module = $this->module->name;

        return $tab->add();
    }

    public function uninstallTab() {
        $id_tab = (int)\Tab::getIdFromClassName(\YooMoneyModule::ADMIN_CONTROLLER);
        if ($id_tab) {
            $tab = new \Tab($id_tab);
            return $tab->delete();
        } else {
            return false;
        }
    }

    public function issetTab() {
        return \Tab::getIdFromClassName(\YooMoneyModule::ADMIN_CONTROLLER) !== false;
    }

}
