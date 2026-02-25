<?php

declare(strict_types=1);

namespace Opencart\Admin\Model\Extension\VenipakShipping\Shipping;

use DOMDocument;

class VenipakShipping extends \Opencart\System\Engine\Model
{
    public function dispatchVenipak($orders_ids, $defined_pack_count = null)
    {
        $this->load->model('catalog/product');
        $this->load->model('sale/order');

        if (! count($orders_ids)) {
            return null;
        }

        $parcel_number = null;
        $query = "
          SELECT
            o.order_id,
        JSON_UNQUOTE(JSON_EXTRACT(o.shipping_method, '$.code')) AS shipping_code,
        JSON_UNQUOTE(JSON_EXTRACT(o.shipping_method, '$.name')) AS shipping_method_name,
            o.shipping_firstname,
            o.shipping_lastname,
            o.shipping_company,
            o.shipping_address_1,
            o.shipping_address_2,
            o.shipping_city,
            o.shipping_postcode,
            c.iso_code_2,
            o.comment,
            JSON_UNQUOTE(JSON_EXTRACT(o.payment_method, '$.code')) AS payment_code,
            o.telephone,
            o.email,
            o.total,
            venipak.status as venipak_status
          FROM
            `" . DB_PREFIX . 'order` o
          INNER JOIN `' . DB_PREFIX . 'country` as c ON o.shipping_country_id = c.country_id
          LEFT JOIN `' . DB_PREFIX . 'venipak_shipping` as venipak ON venipak.order_id = o.order_id
          WHERE
            o.order_id IN (' . implode(',', $orders_ids) . ") AND
            JSON_UNQUOTE(JSON_EXTRACT(o.shipping_method, '$.code')) LIKE '%venipak_shipping.%' ";
        $orders_query = $this->db->query($query);
        $orders_collection = $orders_query->rows;

        if (! count($orders_collection)) {
            return null;
        }

        $pack_collection = [];

        $products_query = $this->db->query('SELECT order_id, order_product_id, `order_product`.quantity, `product`.weight FROM `' . DB_PREFIX . 'order_product` AS order_product, `' . DB_PREFIX . 'product` as product WHERE order_product.product_id = product.product_id AND order_id IN (' . implode(',', $orders_ids) . ')');
        $products_collection = $products_query->rows;

        $venipak_client_id = $this->config->get('shipping_venipak_shipping_client_id');
        $venipak_client_username = $this->config->get('shipping_venipak_shipping_client_username');
        $venipak_client_password = $this->config->get('shipping_venipak_shipping_client_password');
        $venipak_api = $this->config->get('shipping_venipak_shipping_test') ? 'http://venipak.uat.megodata.com/import/send.php' : 'https://go.venipak.lt/import/send.php';

        $venipak_sender_name = $this->config->get('shipping_venipak_shipping_sender_name');
        $venipak_sender_company_code = $this->config->get('shipping_venipak_shipping_sender_company_code');
        $venipak_sender_country = $this->config->get('shipping_venipak_shipping_sender_country');
        $venipak_sender_city = $this->config->get('shipping_venipak_shipping_sender_city');
        $venipak_sender_address = $this->config->get('shipping_venipak_shipping_sender_address');
        $venipak_sender_post_code = $this->config->get('shipping_venipak_shipping_sender_postcode');
        $venipak_sender_contact_person = $this->config->get('shipping_venipak_shipping_sender_contact_person');
        $venipak_sender_contact_tel = $this->config->get('shipping_venipak_shipping_sender_contact_tel');
        $venipak_sender_contact_email = $this->config->get('shipping_venipak_shipping_sender_contact_email');

        $venipak_products_per_pack = $this->config->get('shipping_venipak_shipping_products_per_pack');
        $venipak_initial_tracking_number = $this->config->get('shipping_venipak_shipping_initial_tracking_number');
        $this->load->model('setting/setting');
        $config_shipping_venipak_shipping_last_tracking_number = $this->config->get('shipping_venipak_shipping_last_tracking_number');
        $venipak_tracking_number = $config_shipping_venipak_shipping_last_tracking_number ? $config_shipping_venipak_shipping_last_tracking_number : $venipak_initial_tracking_number;

        $document = new DOMDocument('1.0', 'utf-8');
        $description = $document->createElement('description');
        $description_attr = $document->createAttribute('type');
        $description_attr->value = '1';
        $description->appendChild($description_attr);
        $document->appendChild($description);

        $manifest = $document->createElement('manifest');
        $manifest_attr = $document->createAttribute('title');
        $manifest_value = $venipak_client_id . date('ymd') . '001';
        $manifest_attr->value = $manifest_value;
        $manifest->appendChild($manifest_attr);
        $description->appendChild($manifest);

        $is_shipment = false;
        foreach ($orders_collection as $order_entity) {
            if ($order_entity['venipak_status'] === 'sent') {
                continue;
            }
            $is_shipment = true;
            $shipment = $document->createElement('shipment');
            $manifest->appendChild($shipment);

            $sender = $document->createElement('sender');
            $shipment->appendChild($sender);

            $sender_name = $document->createElement('name', $venipak_sender_name);
            $sender_company_code = $document->createElement('company_code', $venipak_sender_company_code);
            $sender_country = $document->createElement('country', $venipak_sender_country);
            $sender_city = $document->createElement('city', $venipak_sender_city);
            $sender_address = $document->createElement('address', $venipak_sender_address);
            $sender_post_code = $document->createElement('post_code', $venipak_sender_post_code);
            $contact_person = $document->createElement('contact_person', $venipak_sender_contact_person);
            $contact_tel = $document->createElement('contact_tel', $venipak_sender_contact_tel);
            $contact_email = $document->createElement('contact_email', $venipak_sender_contact_email);

            $sender->appendChild($sender_name);
            $sender->appendChild($sender_company_code);
            $sender->appendChild($sender_country);
            $sender->appendChild($sender_city);
            $sender->appendChild($sender_address);
            $sender->appendChild($sender_post_code);
            $sender->appendChild($contact_person);
            $sender->appendChild($contact_tel);
            $sender->appendChild($contact_email);

            $consignee = $document->createElement('consignee');
            $shipment->appendChild($consignee);

            $contact_person = $order_entity['shipping_firstname'] . ' ' . $order_entity['shipping_lastname'];

            if ($order_entity['shipping_code'] === 'venipak_shipping.venipak_shipping_courier') {
                $company_name = $order_entity['shipping_company'];
                $name_value = $company_name ? $company_name : $contact_person;
                $name = $document->createElement('name', $name_value);
                $country = $document->createElement('country', $order_entity['iso_code_2']);
                $city = $document->createElement('city', $order_entity['shipping_city']);
                $address = $document->createElement('address', $order_entity['shipping_address_1'] . ' ' . $order_entity['shipping_address_2']);
                $post_code = $document->createElement('post_code', preg_replace('/\D/', '', $order_entity['shipping_postcode']));
            } elseif (strpos($order_entity['shipping_code'], 'venipak_shipping.venipak_shipping_pickup_') !== false) {
                $pickup_point_id = str_replace('venipak_shipping.venipak_shipping_pickup_', '', $order_entity['shipping_code']);
                $pickups_collection = json_decode(file_get_contents('https://go.venipak.lt/ws/get_pickup_points'), true);
                foreach ($pickups_collection as $pickup_item) {
                    if ($pickup_item['id'] == $pickup_point_id) {
                        $pickup_point_data = $pickup_item;
                        break;
                    }
                }
                $name = $document->createElement('name', $pickup_point_data['name']);
                $company_code = $document->createElement('company_code', $pickup_point_data['code']);
                $consignee->appendChild($company_code);
                $country = $document->createElement('country', $pickup_point_data['country']);
                $city = $document->createElement('city', $pickup_point_data['city']);
                $address = $document->createElement('address', $pickup_point_data['address']);
                $post_code = $document->createElement('post_code', $pickup_point_data['zip']);
            } else {
                return null;
            }

            $consignee->appendChild($name);
            $consignee->appendChild($country);
            $consignee->appendChild($city);
            $consignee->appendChild($address);
            $consignee->appendChild($post_code);

            $contact_person = $document->createElement('contact_person', $contact_person);

            $consignee->appendChild($contact_person);
            $contact_tel = $document->createElement('contact_tel', $order_entity['telephone']);
            $consignee->appendChild($contact_tel);
            $contact_email = $document->createElement('contact_email', $order_entity['email']);
            $consignee->appendChild($contact_email);

            $attribute = $document->createElement('attribute');
            $shipment->appendChild($attribute);

            $shipment_code = $document->createElement('shipment_code', $order_entity['order_id']);
            $attribute->appendChild($shipment_code);
            $doc_no = $document->createElement('doc_no', $order_entity['order_id']);
            $attribute->appendChild($doc_no);

            if ($order_entity['payment_code'] === 'cod') {
                $cod = $document->createElement('cod', $order_entity['total']);
                $attribute->appendChild($cod);
                $cod_type = $document->createElement('cod_type', 'EUR');
                $attribute->appendChild($cod_type);
            }

            if ($order_entity['shipping_code'] === 'venipak_shipping.venipak_shipping_courier') {
                $comment_call = $document->createElement('comment_call', '1');
                $attribute->appendChild($comment_call);
            }

            $order_products = [];
            foreach ($products_collection as $product) {
                if ($product['order_id'] !== $order_entity['order_id']) {
                    continue;
                }
                for ($i = 0; $i < $product['quantity']; $i++) {
                    $order_products[] = $product;
                }
            }

            $pack_count = ceil(count($order_products) / $venipak_products_per_pack);

            if ($defined_pack_count) {
                $pack_count = $defined_pack_count;
                $venipak_products_per_pack = ceil(count($order_products) / $pack_count);
            } else {
                $pack_count = ceil(count($order_products) / $venipak_products_per_pack);
            }

            for ($i = 0; $i < $pack_count; $i++) {
                $pack_nr = $this->formatPackNumber($venipak_tracking_number);
                $venipak_tracking_number++;
                $pack = $document->createElement('pack');
                $pack_no = $document->createElement('pack_no', $pack_nr);
                $pack->appendChild($pack_no);

                if ($i === 0) {
                    $parcel_number = $pack_nr;
                    $this->db->query('
                INSERT INTO ' . DB_PREFIX . "venipak_shipping
                SET order_id = '" . (int) $order_entity['order_id'] . "', tracking = '" . $pack_nr . "'
                ON DUPLICATE KEY UPDATE tracking = '" . $pack_nr . "'
            ");
                }

                $range_from = $i * $venipak_products_per_pack;
                $range_to = $range_from + $venipak_products_per_pack;
                $pack_weight = 0;
                for ($product_key = $range_from; $product_key < $range_to; $product_key++) {
                    if (isset($order_products[$product_key]) === false) {
                        break;
                    }
                    $products = $this->getOrderProducts($order_entity['order_id']);
                    $product_weigth_id = $this->model_catalog_product->getProduct($products[0]['product_id'])['weight_class_id'];
                    $p_weight = $this->weight->convert($order_products[$product_key]['weight'], $product_weigth_id, $this->config->get('shipping_venipak_shipping_weight_class'));
                    $pack_weight = $pack_weight + +$p_weight;
                }
                $weight = $document->createElement('weight', $pack_weight);
                $pack->appendChild($weight);
                $doc_no = $document->createElement('doc_no', $pack_nr);
                $pack->appendChild($doc_no);
                $shipment->appendChild($pack);
                $pack_collection[$order_entity['order_id']][] = $pack_nr;
            }
        }
        if (! $is_shipment) {
            return null;
        }
        $this->model_setting_setting->editSetting('shipping_venipak_shipping_last_tracking_number', ['shipping_venipak_shipping_last_tracking_number' => $venipak_tracking_number]);
        $venipak_xml = $document->saveXML();
        $data = ['user' => $venipak_client_username, 'pass' => $venipak_client_password, 'xml_text' => $venipak_xml];
        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\nReferer: https://www.opencart.com/\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
            ],
        ];
        $context = stream_context_create($options);
        $result_xml_string = file_get_contents($venipak_api, false, $context);
        $result_xml = simplexml_load_string($result_xml_string);
        if ($result_xml->attributes()->type == 'ok') {
            foreach ($orders_ids as $order_id) {
                $this->db->query('UPDATE `' . DB_PREFIX . "venipak_shipping` SET packs = '" . json_encode($pack_collection[$order_id]) . "',  status = 'sent', error_message = '', manifest = '" . $manifest_value . "' WHERE order_id = '" . $order_id . "' ");
            }
            if (count($orders_ids) === 1) {
                return ['data' => $parcel_number, 'status' => 'ok'];
            }

            return ['data' => $result_xml, 'status' => 'ok'];
        } else {
            $this->db->query('UPDATE `' . DB_PREFIX . "venipak_shipping` SET status = 'error', error_message = '" . $result_xml_string . "' WHERE status != 'sent' AND order_id IN (" . implode(',', $orders_ids) . ') ');

            return ['data' => $result_xml, 'status' => 'error'];
        }
    }

    protected function formatPackNumber($id)
    {
        return 'V' . $this->config->get('shipping_venipak_shipping_client_id') . 'E' . str_pad(strval($id), 7, '0', STR_PAD_LEFT);
    }

    protected function getOrderProducts($order_id)
    {
        $query = $this->db->query('SELECT * FROM ' . DB_PREFIX . "order_product WHERE order_id = '" . (int) $order_id . "'");

        return $query->rows;
    }
}
