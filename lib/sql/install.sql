-- ----------------------------
--  Table structure for `config`
-- ----------------------------
DROP TABLE IF EXISTS `config`;
CREATE TABLE `config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(255) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  `date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Records of `config`
-- ----------------------------
BEGIN;
INSERT INTO `config` VALUES ('1', 'stripe_secret_key', '', '2014-12-03 22:13:59'), ('2', 'stripe_publishable_key', '', '2014-12-03 22:13:59'), ('3', 'paypal_environment', 'sandbox', '2014-12-11 12:24:45'), ('4', 'payment_type', 'input', '2014-12-03 22:13:59'), ('5', 'https_redirect', '0', '2014-12-03 22:13:59'), ('6', 'email', '', '2014-12-03 22:13:59'), ('7', 'show_description', '1', '2014-12-03 22:13:59'), ('8', 'page_title', 'Stripe Advanced Payment Terminal', '2014-12-03 22:13:59'), ('9', 'show_billing_address', '1', '2014-12-03 22:13:59'), ('10', 'name', '', '2014-12-04 09:49:55'), ('11', 'enable_paypal', '1', '2014-12-04 12:22:47'), ('12', 'enable_subscriptions', 'stripe_and_paypal', '2014-12-04 14:03:15'), ('13', 'paypal_email', '', '2014-12-04 15:59:49'), ('14', 'subscription_length', '0', '2014-12-08 14:11:49'), ('15', 'subscription_interval', '1', '2014-12-08 14:13:06'), ('16', 'currency', 'USD', '2014-12-29 21:29:16'), ('17', 'enable_trial', '0', '2014-12-31 10:48:23'), ('18', 'trial_days', '7', '2014-12-31 11:03:34'), ('19', 'notification_status', 'check', '2014-12-31 10:48:23'), ('20', 'stripe_whsec_key', '', '2014-12-03 22:13:59') ;
COMMIT;

-- ----------------------------
--  Table structure for `invoices`
-- ----------------------------
DROP TABLE IF EXISTS `invoices`;
CREATE TABLE `invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unique_id` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `description` text,
  `amount` decimal(8,2) DEFAULT NULL,
  `number` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `date_due` date DEFAULT NULL,
  `date_paid` datetime DEFAULT NULL,
  `date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1001 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `items`
-- ----------------------------
DROP TABLE IF EXISTS `items`;
CREATE TABLE `items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `price` decimal(8,2) DEFAULT NULL,
  `date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `payments`
-- ----------------------------
DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `zip` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `amount` decimal(8,2) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `cc_name` varchar(255) DEFAULT NULL,
  `cc_last_4` varchar(255) DEFAULT NULL,
  `stripe_transaction_id` varchar(255) DEFAULT NULL,
  `paypal_transaction_id` varchar(255) DEFAULT NULL,
  `date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `subscriptions`
-- ----------------------------
DROP TABLE IF EXISTS `subscriptions`;
CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unique_id` varchar(255) DEFAULT NULL,
  `stripe_customer_id` varchar(255) DEFAULT NULL,
  `stripe_subscription_id` varchar(255) DEFAULT NULL,
  `paypal_subscription_id` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `zip` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `price` decimal(8,2) DEFAULT NULL,
  `billing_day` int(2) DEFAULT NULL,
  `length` int(4) DEFAULT NULL,
  `interval` int(4) DEFAULT NULL,
  `trial_days` int(4) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `date_trial_ends` date DEFAULT NULL,
  `date_canceled` datetime DEFAULT NULL,
  `date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `invoices`
-- ----------------------------
DROP TABLE IF EXISTS `event`;
CREATE TABLE `event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` varchar(255) DEFAULT NULL,
  `stripe_customer_id` varchar(255) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `type` text,
  `date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;