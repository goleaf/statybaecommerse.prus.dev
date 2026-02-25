<?php

declare(strict_types=1);

namespace Opencart\Catalog\Model\Extension\VenipakShipping\Shipping;

class VenipakShipping extends \Opencart\System\Engine\Model
{
    public function getQuote($address, $order_id = 0)
    {
        $this->load->language('extension/venipak_shipping/shipping/venipak_shipping');
        $getCartDetails = $this->cart->getProducts();
        $product_ids = array_column($getCartDetails, 'product_id');

        $product_ids = array_map('intval', $product_ids);
        $product_id_string = implode(',', array_map([$this->db, 'escape'], $product_ids));

        $query = $this->db->query('
            SELECT COUNT(*) AS total
            FROM ' . DB_PREFIX . 'product
            WHERE venipak_is_locker_excluded = 1
            AND product_id IN (' . $product_id_string . ')
        ');

        $venipak_is_locker_excluded_row_count = $query->row['total'];

        $courier_disabled_count = $this->db->query('
            SELECT COUNT(*) AS total
            FROM ' . DB_PREFIX . 'product
            WHERE venipak_is_delivery_excluded = 1
            AND product_id IN (' . $product_id_string . ')
        ');

        $venipak_is_courier_excluded_row_count = $courier_disabled_count->row['total'];

        $query = $this->db->query('SELECT * FROM ' . DB_PREFIX . "zone_to_geo_zone WHERE country_id = '" . (int) $address['country_id'] . "' AND ( zone_id = '" . (int) $address['zone_id'] . "' OR zone_id = '0')");
        if ($query->num_rows && $this->config->get('shipping_venipak_shipping_geo_zone_id_' . $query->row['geo_zone_id'])) {
            $status = true;
        } else {
            $status = false;
        }

        $method_data = [];

        if ($status) {

            $quote_data = [];
            $is_courier_disabled = $this->config->get('shipping_venipak_shipping_disable_courier_' . $query->row['geo_zone_id']);
            $weight = $this->weight->convert($this->cart->getWeight(), $this->config->get('config_weight_class_id'), $this->config->get('shipping_venipak_shipping_weight_class'));
            $pb_weight_limit = $this->config->get('shipping_venipak_shipping_pb_weight_limit') ?: 30;
            $pp_weight_limit = $this->config->get('shipping_venipak_shipping_pp_weight_limit') ?: 10;
            $shipping_venipak_shipping_disable_locker = ($weight > $pb_weight_limit) ? 'on' : $this->config->get('shipping_venipak_shipping_disable_locker_' . $query->row['geo_zone_id']);
            $shipping_venipak_shipping_disable_pickup = ($weight > $pp_weight_limit) ? 'on' : $this->config->get('shipping_venipak_shipping_disable_pickup_' . $query->row['geo_zone_id']);
            $postbox_dim = [
                $this->config->get('shipping_venipak_shipping_length_limit'),
                $this->config->get('shipping_venipak_shipping_width_limit'),
                $this->config->get('shipping_venipak_shipping_height_limit'),
            ];
            rsort($postbox_dim);
            $venipak_units = $this->config->get('shipping_venipak_shipping_dimmension_units');
            if ($order_id) {
                $items_in_cart = $this->getOrderProducts($order_id);
            } else {
                $items_in_cart = $this->cart->getProducts();
            }

            foreach ($items_in_cart as $cart_item) {

                $length = $this->length->convert($cart_item['length'], $cart_item['length_class_id'], $venipak_units);
                $width = $this->length->convert($cart_item['width'], $cart_item['length_class_id'], $venipak_units);
                $height = $this->length->convert($cart_item['height'], $cart_item['length_class_id'], $venipak_units);
                $item_dim = [$length, $width, $height];
                rsort($item_dim);
                $lpexpess_ab = array_sum($item_dim);

                if ($item_dim[0] > 300 || $lpexpess_ab > 430) {

                    $shipping_venipak_shipping_disable_locker = 'on';
                    $shipping_venipak_shipping_disable_pickup = 'on';
                    $is_courier_disabled = 'on';
                    break;
                }

                foreach ($item_dim as $k => $v) {

                    if ($v >= $postbox_dim[$k]) {

                        $shipping_venipak_shipping_disable_locker = 'on';
                        $shipping_venipak_shipping_disable_pickup = 'on';
                        break;
                    }
                }
            }

            $courrier_free = trim($this->config->get('shipping_venipak_shipping_courrier_free_' . $query->row['geo_zone_id']));
            $total_cost = $this->cart->getTotal();

            if (empty($courrier_free) || $courrier_free > $total_cost) {

                $courier_cost = $this->config->get('shipping_venipak_shipping_cost_courier_' . $query->row['geo_zone_id']);
                $courier_rates = explode(',', $courier_cost);

                foreach ($courier_rates as $rate) {
                    $data = explode(':', $rate);
                    if ($data[0] >= $weight) {
                        if (isset($data[1])) {
                            $courier_cost = $data[1];
                        }
                        break;
                    }
                }

            } else {

                $courier_cost = 0;
            }
            $pickup_free = trim($this->config->get('shipping_venipak_shipping_pickup_free_' . $query->row['geo_zone_id']));
            if (empty($pickup_free) || $pickup_free > $total_cost) {

                $pickup_cost = $this->config->get('shipping_venipak_shipping_cost_pickup_' . $query->row['geo_zone_id']);
                $pickup_rates = explode(',', $pickup_cost);
                foreach ($pickup_rates as $rate) {
                    $data = explode(':', $rate);
                    if ($data[0] >= $weight) {
                        if (isset($data[1])) {
                            $pickup_cost = $data[1];
                        }
                        break;
                    }
                }
            } else {

                $pickup_cost = 0;
            }

            // Removed static pickup method

            if (empty($is_courier_disabled) && ! ($venipak_is_courier_excluded_row_count > 0)) {
                $quote_data['venipak_shipping_courier'] = [
                    'code'         => 'venipak_shipping.venipak_shipping_courier',
                    'name'         => $this->config->get('shipping_venipak_shipping_method_title_courier_' . $query->row['geo_zone_id']),
                    'cost'         => $courier_cost,
                    'tax_class_id' => $this->config->get('shipping_venipak_shipping_tax_class_id'),
                    'text'         => $this->currency->format($this->tax->calculate($courier_cost, $this->config->get('shipping_venipak_shipping_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency']),
                ];
            }
            if ((empty($shipping_venipak_shipping_disable_locker) || empty($shipping_venipak_shipping_disable_pickup)) && ! ($venipak_is_locker_excluded_row_count > 0)) {
                $pickup_points = $this->getVenipakPickupsList($address['iso_code_2'], $order_id);
                if (! empty($pickup_points)) {

                    foreach ($pickup_points as $point) {

                        $quote_data['venipak_shipping_pickup_' . $point['id']] = [
                            'code'         => 'venipak_shipping.venipak_shipping_pickup_' . $point['id'],
                            'name'         => $this->config->get('shipping_venipak_shipping_method_title_pickup_' . $query->row['geo_zone_id']) . '-' . $point['display_name'] . ' (' . $point['address'] . ')',
                            'cost'         => $pickup_cost,
                            'tax_class_id' => $this->config->get('shipping_venipak_shipping_tax_class_id'),
                            'text'         => $this->currency->format($this->tax->calculate($pickup_cost, $this->config->get('shipping_venipak_shipping_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency']),
                        ];
                    }
                }
            }

            // if (empty($shipping_venipak_shipping_disable_locker) || empty($shipping_venipak_shipping_disable_pickup)) {
            //   // Fetch dynamic pickup points from the new service endpoint
            //   $pickup_points = $this->getDynamicPickupPoints($address,  $order_id);
            //   if (!empty($pickup_points)) {
            //       foreach ($pickup_points as $point) {
            //           $quote_data['venipak_shipping_pickup_' . $point['id']] = array(
            //               'code'         => 'venipak_shipping.venipak_shipping_pickup_' . $point['id'],
            //               'title'        => round($point['distance'], 2) . 'km ' . $point['name'],
            //               'cost'         => $pickup_cost,
            //               'tax_class_id' => $this->config->get('shipping_venipak_shipping_tax_class_id'),
            //               'text'         => $this->currency->format($this->tax->calculate($pickup_cost, $this->config->get('shipping_venipak_shipping_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'])
            //           );
            //       }
            //   }
            // }

            $method_data = [
                'code'       => 'venipak_shipping',
                'name'       => $order_id ? $this->config->get('shipping_venipak_shipping_method_title_' . $query->row['geo_zone_id']) : $this->config->get('shipping_venipak_shipping_method_title_' . $query->row['geo_zone_id']),
                'quote'      => $quote_data,
                'sort_order' => $this->config->get('shipping_venipak_shipping_sort_order_' . $query->row['geo_zone_id']),
                'error'      => false,
            ];
        }

        return $method_data;
    }

    private function getVenipakPickupsList($country, $order_id)
    {
        // Venipak API URL for fetching pickup points
        $url = 'https://go.venipak.lt/ws/get_pickup_points?country=' . $country;

        // Initialize cURL session
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Skip SSL verification if needed
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Set timeout for the request

        // Execute the cURL session and fetch response
        $response = curl_exec($ch);

        // Check for any errors
        if (curl_errno($ch)) {
            return false;
        }

        // Close the cURL session
        curl_close($ch);

        // Decode the JSON response into an associative array
        $pickup_points = json_decode($response, true);

        // Return the list of pickup points
        return $pickup_points;
    }

    private function getDynamicPickupPoints($address, $order_id)
    {
        $country = $address['iso_code_2'];
        $full_address = $address['address_1'] . ', ' . $address['city'] . ', ' . $address['postcode'];

        $order_details = $this->getOrderDetails($order_id);
        // $order_details['order_id'] = $order_id; // Add order ID to the request data for tracking
        $response = $this->sendRequestToPickupService($full_address, $country, $order_details);

        return $response;
    }

    private function getOrderDetails($order_id)
    {
        $total_weight = 0;

        // Check if an order ID is provided (this means the order has been placed)
        if ($order_id) {
            $this->load->model('sale/order');
            $order_info = $this->model_sale_order->getOrder($order_id);
            $order_products = $this->model_sale_order->getOrderProducts($order_id);

            foreach ($order_products as $product) {
                $total_weight += $product['weight'] * $product['quantity'];
            }

            return [
                'first_name'   => $order_info['firstname'],
                'last_name'    => $order_info['lastname'],
                'email'        => $order_info['email'],
                'phone'        => $order_info['telephone'],
                'total_weight' => $total_weight,
                'order_id'     => $order_id,
            ];
        } else {
            // If there's no order ID, get details from session (checkout page scenario)
            $first_name = $this->customer->getFirstName();
            $last_name = $this->customer->getLastName();
            $email = $this->customer->getEmail();
            $phone = $this->customer->getTelephone();

            // If customer is not logged in, you might need to fetch the guest checkout data
            if (! $this->customer->isLogged() && isset($this->session->data['guest'])) {
                $first_name = $this->session->data['guest']['firstname'];
                $last_name = $this->session->data['guest']['lastname'];
                $email = $this->session->data['guest']['email'];
                $phone = $this->session->data['guest']['telephone'];
            }

            // Calculate the total weight of the items in the cart
            foreach ($this->cart->getProducts() as $product) {
                $total_weight += $product['weight'] * $product['quantity'];
            }

            return [
                'first_name'   => $first_name,
                'last_name'    => $last_name,
                'email'        => $email,
                'phone'        => $phone,
                'total_weight' => $total_weight,
                'order_id'     => $order_id,
            ];
        }
    }

    private function sendRequestToPickupService($address, $country, $order_details)
    {
        $url = 'https://geolocation-service-venipak.asist.lt/get-nearest-pickups';
        $params = [
            'address'      => $address,
            'country'      => $country,
            'first_name'   => $order_details['first_name'],
            'last_name'    => $order_details['last_name'],
            'email'        => $order_details['email'],
            'phone'        => $order_details['phone'],
            'total_weight' => $order_details['total_weight'],
            'order_id'     => $order_details['order_id'],
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($curl);
        if ($response === false) {
            exit('Curl error: ' . curl_error($curl));
        }
        curl_close($curl);

        return json_decode($response, true);
    }

    private function getOrderProducts($order_id)
    {
        $this->load->model('sale/order');

        return $this->model_sale_order->getOrderProducts($order_id);
    }
}
