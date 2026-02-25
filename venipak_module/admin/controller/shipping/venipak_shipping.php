<?php

declare(strict_types=1);

namespace Opencart\Admin\Controller\Extension\VenipakShipping\Shipping;

class VenipakShipping extends \Opencart\System\Engine\Controller
{
    private $error = [];

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->load->controller('extension/venipak_shipping/event/venipak');
    }

    public function install(): void
    {
        $this->db->query('
			CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . 'venipak_shipping` (
				`venipak_id` INT NOT NULL AUTO_INCREMENT ,
				`order_id` INT NOT NULL ,
				`venipak_shipping_pickup_point` TEXT NOT NULL ,
				`status` VARCHAR(10) NOT NULL ,
				`tracking` VARCHAR(14) NOT NULL ,
				`manifest` VARCHAR(14) NOT NULL ,
				`packs` TEXT NOT NULL,
				`error_message` TEXT NOT NULL,
				PRIMARY KEY (`venipak_id`),
				UNIQUE (`order_id`))
				CHARACTER SET utf8 COLLATE utf8_general_ci;
		');

        $this->db->query('
			CREATE TABLE IF NOT EXISTS`' . DB_PREFIX . "venipak_shipping_admin_setting` (
			`setting_id` int NOT NULL AUTO_INCREMENT,
			`store_id` int NOT NULL DEFAULT '0',
			`code` varchar(128) COLLATE utf8mb4_general_ci NOT NULL,
			`key` varchar(128) COLLATE utf8mb4_general_ci NOT NULL,
			`value` text COLLATE utf8mb4_general_ci NOT NULL,
			`serialized` tinyint(1) NOT NULL DEFAULT '0',
			PRIMARY KEY (`setting_id`)
		)CHARACTER SET utf8 COLLATE utf8_general_ci;
		");

        $query = $this->db->query('SELECT * FROM `' . DB_PREFIX . 'venipak_shipping_admin_setting`');
        if ($query->num_rows > 0) {
            foreach ($query->rows as $setting) {
                $this->db->query('INSERT INTO `' . DB_PREFIX . "setting` SET 
				`store_id` = '" . (int) $setting['store_id'] . "',
				`code` = '" . $this->db->escape($setting['code']) . "',
				`key` = '" . $this->db->escape($setting['key']) . "',
				`value` = '" . $this->db->escape($setting['value']) . "',
				`serialized` = '" . $this->db->escape($setting['serialized']) . "'");
            }
        }

        $this->db->query('ALTER TABLE `' . DB_PREFIX . 'session` CHANGE `data` `data` LONGTEXT CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL;');

        $query = $this->db->query('SHOW COLUMNS FROM `' . DB_PREFIX . "product` LIKE 'venipak_is_locker_excluded'");
        if (! $query->num_rows) {
            $this->db->query('ALTER TABLE `' . DB_PREFIX . 'product` ADD `venipak_is_locker_excluded` TINYINT(1) NOT NULL DEFAULT 0');
        }

        $deliveryOption = $this->db->query('SHOW COLUMNS FROM `' . DB_PREFIX . "product` LIKE 'venipak_is_delivery_excluded'");
        if (! $deliveryOption->num_rows) {
            $this->db->query('ALTER TABLE `' . DB_PREFIX . 'product` ADD `venipak_is_delivery_excluded` TINYINT(1) NOT NULL DEFAULT 0');
        }
        $this->load->model('setting/event');

        $events = [
            [
                'code'        => 'venipak_order_info',
                'trigger'     => 'admin/view/sale/order_info/before',
                'description' => 'venipak order info',
                'sort_order'  => '1',
                'action'      => 'extension/venipak_shipping/event/venipak.orderInfo',
                'status'      => true,
            ],
            [
                'code'        => 'venipak_order_list',
                'trigger'     => 'admin/view/sale/order_list/before',
                'description' => 'venipak order list',
                'sort_order'  => '1',
                'action'      => 'extension/venipak_shipping/event/venipak.orderList',
                'status'      => true,
            ],
            [
                'code'        => 'venipak_label_shipping_buttons',
                'trigger'     => 'admin/view/sale/order/before',
                'description' => 'venipak label shipping buttons',
                'sort_order'  => '1',
                'action'      => 'extension/venipak_shipping/event/venipak.orderLabelButtons',
                'status'      => true,
            ],
            [
                'code'        => 'venipak_load_scripts',
                'trigger'     => 'catalog/controller/common/header/before',
                'description' => 'venipak load scripts',
                'sort_order'  => '1',
                'action'      => 'extension/venipak_shipping/event/venipak.venipakLoadScripts',
                'status'      => true,
            ],
            [
                'code'        => 'venipak_exclude_locker_checkbox_product_form',
                'trigger'     => 'admin/view/catalog/product_form/before',
                'description' => 'venipak exclude locker checkbox product form',
                'sort_order'  => '1',
                'action'      => 'extension/venipak_shipping/event/venipak.addExcludeLockerCheckboxProductForm',
                'status'      => true,
            ],
            [
                'code'        => 'venipak_exclude_locker_edit_data',
                'trigger'     => 'admin/model/catalog/product/editProduct/after',
                'description' => 'venipak exclude locker edit data',
                'sort_order'  => '1',
                'action'      => 'extension/venipak_shipping/event/venipak.venipakExcludedFromLockerEditData',
                'status'      => true,
            ],
            [
                'code'        => 'venipak_exclude_locker_add_data',
                'trigger'     => 'admin/model/catalog/product/addProduct/after',
                'description' => 'venipak exclude locker add data',
                'sort_order'  => '1',
                'action'      => 'extension/venipak_shipping/event/venipak.venipakExcludedFromLockerAddData',
                'status'      => true,
            ],
            [
                'code'        => 'venipak_excluded_locker_data_product_list',
                'trigger'     => 'admin/view/catalog/product_list/before',
                'description' => 'venipak exclude locker data product list',
                'sort_order'  => '1',
                'action'      => 'extension/venipak_shipping/event/venipak.excludedShippingOptionsInProductList',
                'status'      => true,
            ],
            [
                'code'        => 'venipak_edit_product_list',
                'trigger'     => 'admin/view/catalog/product_list/before',
                'description' => 'venipak edit product list',
                'sort_order'  => '1',
                'action'      => 'extension/venipak_shipping/event/venipak.venipakEditProductList',
                'status'      => true,
            ],
            [
                'code'        => 'venipak_additional_cost_for_cod',
                'trigger'     => 'catalog/model/extension/opencart/total/shipping/getTotal/after',
                'description' => 'Apply Venipak additional cost for COD',
                'sort_order'  => 1,
                'action'      => 'extension/venipak_shipping/event/venipak.venipakAdditionalCostToCod',
                'status'      => true,
            ], [
                'code'        => 'venipak_admin_settings',
                'trigger'     => 'admin/controller/extension/shipping.uninstall/before',
                'description' => 'Venipak admin configurations',
                'sort_order'  => 1,
                'action'      => 'extension/venipak_shipping/event/venipak.saveAdminConfiguration',
                'status'      => true,
            ],
        ];
        foreach ($events as $event) {
            $existingEvent = $this->model_setting_event->getEventByCode($event['code']);
            if ($existingEvent) {
                continue;
            }

            // Add the event
            $this->model_setting_event->addEvent(
                $event
            );
        }
    }

    public function uninstall(): void
    {
        $this->load->model('setting/event');
        $this->model_setting_event->deleteEventByCode('venipak_order_info');
        $this->model_setting_event->deleteEventByCode('venipak_order_list');
        $this->model_setting_event->deleteEventByCode('venipak_label_shipping_buttons');
        $this->model_setting_event->deleteEventByCode('venipak_load_scripts');
        $this->model_setting_event->deleteEventByCode('venipak_exclude_locker_checkbox_product_form');
        $this->model_setting_event->deleteEventByCode('venipak_exclude_locker_add_data');
        $this->model_setting_event->deleteEventByCode('venipak_exclude_locker_edit_data');
        $this->model_setting_event->deleteEventByCode('venipak_excluded_locker_data_product_list');
        $this->model_setting_event->deleteEventByCode('venipak_edit_product_list');
        $this->model_setting_event->deleteEventByCode('venipak_additional_cost_for_cod');
        $this->model_setting_event->deleteEventByCode('venipak_admin_settings');
    }

    public function index(): void
    {
        $this->load->language('extension/venipak_shipping/shipping/venipak_shipping');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $last_tracking_number['shipping_venipak_shipping_last_tracking_number'] = $this->request->post['shipping_venipak_shipping_last_tracking_number'];
            unset($this->request->post['shipping_venipak_shipping_last_tracking_number']);
            $this->model_setting_setting->editSetting('shipping_venipak_shipping', $this->request->post);
            $this->model_setting_setting->editSetting('shipping_venipak_shipping_last_tracking_number', $last_tracking_number);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true),
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/venipak_shipping/shipping/venipak_shipping', 'user_token=' . $this->session->data['user_token'], true),
        ];

        $this->load->model('localisation/tax_class');

        $data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

        $this->load->model('localisation/geo_zone');
        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        $data['action'] = $this->url->link('extension/venipak_shipping/shipping/venipak_shipping', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true);

        foreach ($data['geo_zones'] as $item) {
            $geo_zone_id = $item['geo_zone_id'];

            $data['shipping_venipak_shipping_sort_order_' . $geo_zone_id] = $this->getParam('shipping_venipak_shipping_sort_order_' . $geo_zone_id);
            $data['shipping_venipak_shipping_method_title_' . $geo_zone_id] = $this->getParam('shipping_venipak_shipping_method_title') ?: 'Venipak';
            $data['shipping_venipak_shipping_method_title_courier_' . $geo_zone_id] = $this->getParam('shipping_venipak_shipping_method_title_courier_' . $geo_zone_id) ?: 'Courier';
            $data['shipping_venipak_shipping_method_title_pickup_' . $geo_zone_id] = $this->getParam('shipping_venipak_shipping_method_title_pickup_' . $geo_zone_id) ?: 'Pickup';

            $data['shipping_venipak_shipping_cost_courier_' . $geo_zone_id] = $this->getParam('shipping_venipak_shipping_cost_courier_' . $geo_zone_id) != null ? $this->getParam('shipping_venipak_shipping_cost_courier_' . $geo_zone_id) : 0;
            $data['shipping_venipak_shipping_cost_pickup_' . $geo_zone_id] = $this->getParam('shipping_venipak_shipping_cost_pickup_' . $geo_zone_id) != null ? $this->getParam('shipping_venipak_shipping_cost_pickup_' . $geo_zone_id) : 0;
            $data['shipping_venipak_shipping_pickup_charges_for_cod_' . $geo_zone_id] = $this->getParam('shipping_venipak_shipping_pickup_charges_for_cod_' . $geo_zone_id) != null ? $this->getParam('shipping_venipak_shipping_pickup_charges_for_cod_' . $geo_zone_id) : 0;

            $data['shipping_venipak_shipping_courrier_charges_for_cod_' . $geo_zone_id] = $this->getParam('shipping_venipak_shipping_courrier_charges_for_cod_' . $geo_zone_id) != null ? $this->getParam('shipping_venipak_shipping_courrier_charges_for_cod_' . $geo_zone_id) : 0;

            $data['shipping_venipak_shipping_courrier_free_' . $geo_zone_id] = $this->getParam('shipping_venipak_shipping_courrier_free_' . $geo_zone_id);
            $data['shipping_venipak_shipping_pickup_free_' . $geo_zone_id] = $this->getParam('shipping_venipak_shipping_pickup_free_' . $geo_zone_id);
            $data['shipping_venipak_shipping_geo_zone_id_' . $geo_zone_id] = $geo_zone_id;
            $data['shipping_venipak_shipping_disable_pickup_' . $geo_zone_id] = $this->getParam('shipping_venipak_shipping_disable_pickup_' . $geo_zone_id);
            $data['shipping_venipak_shipping_disable_locker_' . $geo_zone_id] = $this->getParam('shipping_venipak_shipping_disable_locker_' . $geo_zone_id);
            $data['shipping_venipak_shipping_disable_courier_' . $geo_zone_id] = $this->getParam('shipping_venipak_shipping_disable_courier_' . $geo_zone_id);
        }

        $data['shipping_venipak_shipping_status'] = $this->getParam('shipping_venipak_shipping_status');
        $data['shipping_venipak_shipping_client_id'] = $this->getParam('shipping_venipak_shipping_client_id');
        $data['shipping_venipak_shipping_client_username'] = $this->getParam('shipping_venipak_shipping_client_username');
        $data['shipping_venipak_shipping_client_password'] = $this->getParam('shipping_venipak_shipping_client_password');
        $data['shipping_venipak_shipping_test'] = $this->getParam('shipping_venipak_shipping_test');
        $data['shipping_venipak_shipping_weight_class'] = $this->getParam('shipping_venipak_shipping_weight_class') ?: 1;
        $data['shipping_venipak_shipping_pb_weight_limit'] = $this->getParam('shipping_venipak_shipping_pb_weight_limit') ?: 30;
        $data['shipping_venipak_shipping_pp_weight_limit'] = $this->getParam('shipping_venipak_shipping_pp_weight_limit') ?: 10;

        $data['shipping_venipak_shipping_length_limit'] = $this->getParam('shipping_venipak_shipping_length_limit') ?: 61;
        $data['shipping_venipak_shipping_width_limit'] = $this->getParam('shipping_venipak_shipping_width_limit') ?: 35;
        $data['shipping_venipak_shipping_height_limit'] = $this->getParam('shipping_venipak_shipping_height_limit') ?: 75;
        $data['shipping_venipak_shipping_dimmension_units'] = $this->getParam('shipping_venipak_shipping_dimmension_units') ?: 1;

        $data['shipping_venipak_shipping_stickers'] = $this->getParam('shipping_venipak_shipping_stickers') ?: 'A4';
        $data['shipping_venipak_shipping_tax_class_id'] = $this->getParam('shipping_venipak_shipping_tax_class_id');
        $data['shipping_venipak_shipping_initial_tracking_number'] = $this->getParam('shipping_venipak_shipping_initial_tracking_number') ?: 1000001;
        $data['shipping_venipak_shipping_last_tracking_number'] =
        $this->getParam('shipping_venipak_shipping_last_tracking_number')
        ?: $this->getParam('shipping_venipak_shipping_initial_tracking_number')
        ?: 1000001;
        $data['shipping_venipak_shipping_products_per_pack'] = $this->getParam('shipping_venipak_shipping_products_per_pack') ?: 1000;

        $data['shipping_venipak_shipping_sender_name'] = $this->getParam('shipping_venipak_shipping_sender_name');
        $data['shipping_venipak_shipping_sender_company_code'] = $this->getParam('shipping_venipak_shipping_sender_company_code');
        $data['shipping_venipak_shipping_sender_country'] = $this->getParam('shipping_venipak_shipping_sender_country');
        $data['shipping_venipak_shipping_sender_city'] = $this->getParam('shipping_venipak_shipping_sender_city');
        $data['shipping_venipak_shipping_sender_address'] = $this->getParam('shipping_venipak_shipping_sender_address');
        $data['shipping_venipak_shipping_sender_postcode'] = $this->getParam('shipping_venipak_shipping_sender_postcode');
        $data['shipping_venipak_shipping_sender_contact_person'] = $this->getParam('shipping_venipak_shipping_sender_contact_person');
        $data['shipping_venipak_shipping_sender_contact_tel'] = $this->getParam('shipping_venipak_shipping_sender_contact_tel');
        $data['shipping_venipak_shipping_sender_contact_email'] = $this->getParam('shipping_venipak_shipping_sender_contact_email');

        $data['shipping_venipak_shipping_is_map_enabled'] = $this->getParam('shipping_venipak_shipping_is_map_enabled');
        $data['shipping_venipak_shipping_google_api_key'] = $this->getParam('shipping_venipak_shipping_google_api_key') ?: 'AIzaSyBlTo9ELuhn8u03BQN0w6RdmKFgVfJhnqE';
        $data['shipping_venipak_shipping_is_clusters_enabled'] = $this->getParam('shipping_venipak_shipping_is_clusters_enabled');

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->load->model('localisation/weight_class');
        $data['weight_classes'] = $this->model_localisation_weight_class->getWeightClasses();
        $this->load->model('localisation/length_class');
        $data['length_classes'] = $this->model_localisation_length_class->getLengthClasses();
        //  echo "<pre>";
        //  print_r($data);
        //  die;
        $this->response->setOutput($this->load->view('extension/venipak_shipping/shipping/venipak_shipping', $data));
    }

    protected function getParam($key)
    {
        return isset($this->request->post[$key])
            ? $this->request->post[$key]
            : $this->config->get($key);
    }

    protected function validate()
    {
        if (! $this->user->hasPermission('modify', 'extension/venipak_shipping/shipping/venipak_shipping')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return ! $this->error;
    }

    public function venipakDispatch()
    {
        $this->load->language('sale/order');

        $json = [];

        if (! $this->user->hasPermission('modify', 'sale/order')) {
            $json['error'] = $this->language->get('error_permission');
        } elseif (isset($this->request->get['order_id'])) {
            if (isset($this->request->get['order_id'])) {
                $order_id = $this->request->get['order_id'];
            } else {
                $order_id = 0;
            }

            $pack_count = $this->request->get['pack_count'];

            $this->load->model('extension/venipak_shipping/shipping/venipak_shipping');
            $dispatch_result = $this->model_extension_venipak_shipping_shipping_venipak_shipping->dispatchVenipak([$order_id], $pack_count);
            if ($dispatch_result && $dispatch_result['status'] === 'ok') {
                $json['venipak_tracking_number'] = $dispatch_result['data'];
            } elseif ($dispatch_result) {
                $json['error'] = $dispatch_result['data'];
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function venipakSendOrders()
    {
        $this->load->language('sale/order');
        $orders = [];

        if (isset($this->request->post['selected'])) {
            $orders = $this->request->post['selected'];
        } elseif (isset($this->request->get['order_id'])) {
            $orders[] = $this->request->get['order_id'];
        }

        $this->load->model('extension/venipak_shipping/shipping/venipak_shipping');
        $dispatch_result = $this->model_extension_venipak_shipping_shipping_venipak_shipping->dispatchVenipak($orders);

        if ($dispatch_result && $dispatch_result['status'] === 'ok') {
            $this->session->data['success'] = $this->language->get('venipak_send_orders_success');
        } elseif (! $this->user->hasPermission('modify', 'sale/order')) {
            $this->session->data['success'] = $this->language->get('error_permission');
        } elseif (! $orders) {
            $this->session->data['success'] = $this->language->get('error_zero_orders_selected');
        } elseif ($dispatch_result && $dispatch_result['status'] === 'error') {
            $dispatch_result_error = $dispatch_result['data']->error->text;
            $dispatch_result_error_string = json_encode($dispatch_result_error);
            $dispatch_result_error_string_clean = preg_replace('/[^a-zA-Z]/', ' ', $dispatch_result_error_string);

            $this->session->data['success'] = 'Error. ' . $dispatch_result_error_string_clean;
        } else {
            $this->session->data['success'] = $this->language->get('error_sent_order_selected');
        }

        $this->response->redirect($this->url->link('sale/order', '&user_token=' . $this->session->data['user_token'], 'SSL'));
    }

    public function venipakGetLabels()
    {
        $this->load->model('setting/setting');

        if (! $this->user->hasPermission('modify', 'sale/order')) {
            $json['error'] = $this->language->get('error_permission');
        }
        $orders = [];

        if (isset($this->request->post['selected'])) {
            $orders = $this->request->post['selected'];
        } elseif (isset($this->request->get['order_id'])) {
            $orders[] = $this->request->get['order_id'];
        }

        if (count($orders) === 0) {
            $this->session->data['success'] = $this->language->get('error_zero_orders_selected');
            $this->response->redirect($this->url->link('sale/order', '&user_token=' . $this->session->data['user_token'], 'SSL'));

            return;
        }

        $venipak_orders_query = $this->db->query('SELECT packs FROM ' . DB_PREFIX . 'venipak_shipping WHERE order_id IN (' . implode(',', $orders) . ") AND status = 'sent'");
        $orders_collection = $venipak_orders_query->rows;

        foreach ($orders_collection as $order) {
            $packs = json_decode($order['packs'], true);
            foreach ($packs as $pack) {
                $venipak_order_tracking_number[] = $pack;
            }
        }

        $venipak_client_username = $this->config->get('shipping_venipak_shipping_client_username');
        $venipak_client_password = $this->config->get('shipping_venipak_shipping_client_password');
        $venipak_page_size = $this->config->get('shipping_venipak_shipping_stickers');

        $venipak_api = $this->config->get('shipping_venipak_shipping_test') ? 'http://venipak.uat.megodata.com/ws/print_label' : 'https://go.venipak.lt/ws/print_label';

        $body = [
            'user'    => $venipak_client_username,
            'pass'    => $venipak_client_password,
            'pack_no' => $venipak_order_tracking_number,
            'type'    => $venipak_page_size,
        ];

        $body_string = http_build_query($body);

        $ch = curl_init();

        // set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $venipak_api);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body_string);

        // So that curl_exec returns the contents of the cURL; rather than echoing it
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $label_result = curl_exec($ch);

        header('Content-type:application/pdf');
        echo $label_result;
    }

    public function updatePickupCheckbox()
    {
        $this->load->model('catalog/product');
        $product_id = (int) $this->request->post['product_id'];
        $venipak_is_locked_excluded = (int) $this->request->post['pickup_value'];
        $query = 'UPDATE ' . DB_PREFIX . 'product SET `venipak_is_locker_excluded` = ' . (int) $venipak_is_locked_excluded . ' WHERE `product_id` = ' . (int) $product_id;
        $this->db->query($query);

        return true;
    }

    public function updateDeliveryCheckbox()
    {
        $this->load->model('catalog/product');
        $product_id = (int) $this->request->post['product_id'];
        $venipak_is_delivery_excluded = (int) $this->request->post['pickup_value'];
        $query = 'UPDATE ' . DB_PREFIX . 'product SET `venipak_is_delivery_excluded` = ' . (int) $venipak_is_delivery_excluded . ' WHERE `product_id` = ' . (int) $product_id;
        $this->db->query($query);

        return true;
    }
}
