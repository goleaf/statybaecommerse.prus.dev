<?php

declare(strict_types=1);

namespace Opencart\Catalog\Model\Extension\VenipakShipping\Total;

class VenipakShippingCod extends \Opencart\System\Engine\Model
{
    public function getTotal(&$totals = '', &$taxes = '', &$total = '')
    {
        if ($this->cart->hasShipping() && isset($this->session->data['shipping_method'])) {
            $this->load->model('setting/setting');
            if (isset($this->session->data['shipping_address'])) {
                $shipping_address = $this->session->data['shipping_address'];
                $country_id = (int) $shipping_address['country_id'];
                $zone_id = (int) $shipping_address['zone_id'];
                $query = $this->db->query('
        SELECT geo_zone_id
        FROM ' . DB_PREFIX . "zone_to_geo_zone
        WHERE country_id = '" . (int) $country_id . "'
        AND (zone_id = '" . (int) $zone_id . "' OR zone_id = '0')
    ");
                $geo_zone_id = $query->row['geo_zone_id'] ?? '';
                if (strpos($this->session->data['shipping_method']['code'], 'venipak_shipping_pickup') !== false) {
                    $pickup_free = trim($this->config->get('shipping_venipak_shipping_pickup_free_' . $geo_zone_id));
                    if (empty($pickup_free) || $pickup_free > $total) {
                        $setting_key = 'shipping_venipak_shipping_pickup_charges_for_cod_' . $geo_zone_id;
                        $query = $this->db->query('
    SELECT value
    FROM ' . DB_PREFIX . "setting
    WHERE `key` = '" . $this->db->escape($setting_key) . "'
");
                        if ($query->num_rows > 0) {
                            $charges = $query->row['value'];
                            $shipping_code = $this->session->data['shipping_method']['code'];
                            if (strpos($shipping_code, 'venipak_shipping.venipak_shipping_pickup') === 0 && isset($this->session->data['payment_method']) && $this->session->data['payment_method']['code'] == 'cod.cod') {
                                $total += $charges;
                                foreach ($totals as &$data) {
                                    if ($data['code'] == 'shipping') {
                                        $data['value'] += $charges;
                                        break;
                                    }
                                }
                            }

                        }
                    }
                }

                if (strpos($this->session->data['shipping_method']['code'], 'venipak_shipping_courier') !== false) {
                    $courrier_free = trim($this->config->get('shipping_venipak_shipping_courrier_free_' . $geo_zone_id));
                    if (empty($courrier_free) || $courrier_free > $total) {
                        $setting_key = 'shipping_venipak_shipping_courrier_charges_for_cod_' . $geo_zone_id;
                        $query = $this->db->query('
SELECT value
FROM ' . DB_PREFIX . "setting
WHERE `key` = '" . $this->db->escape($setting_key) . "'
");
                        if ($query->num_rows > 0) {
                            $charges = $query->row['value'];
                            $shipping_code = $this->session->data['shipping_method']['code'];
                            if (strpos($shipping_code, 'venipak_shipping.venipak_shipping_courier') === 0 && isset($this->session->data['payment_method']) && $this->session->data['payment_method']['code'] == 'cod.cod') {
                                $total += $charges;
                                foreach ($totals as &$data) {
                                    if ($data['code'] == 'shipping') {
                                        $data['value'] += $charges;
                                        break;
                                    }
                                }
                            }

                        }
                    }
                }

            }
        }
    }
}
