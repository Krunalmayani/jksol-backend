<?php

class Mdl_common extends CI_Model
{

    // [COMMON METHOD]
    function select($option)
    {
        if (isset($option['select']) && !empty($option['select'])) {
            $this->db->select($option['select']);
        } else {
            $this->db->select('*');
        }

        $this->db->from($option['from']);

        if (isset($option['where']) && !empty($option['where'])) {
            $this->db->where($option['where']);
        }

        if (isset($option['between']) && isset($option['between']['key'])) {
            $between_key = $option['between']['key'];
            $between_min = isset($option['between']['min']) ? $option['between']['min'] : 0;
            $between_max = isset($option['between']['max']) ? $option['between']['max'] : 0;
            $this->db->where("$between_key BETWEEN '$between_min' AND '$between_max'");
        }

        if (isset($option['pagination']) && !empty($option['pagination'])) {
            $offset = isset($option['pagination']['offset']) ? $option['pagination']['offset'] : 0;
            $this->db->limit($option['pagination']['limit'], $offset);
        }

        if (isset($option['order_by']['key']) && !empty($option['order_by']['key'])) {
            $order = isset($option['order_by']['order']) ? $option['order_by']['order'] : 'ASC';
            $this->db->order_by($option['order_by']['key'], $order);
        }

        $info = $this->db->get();
        return $info->result_array();
    }

    function update($option)
    {
        if (isset($option['where']) && !empty($option['where'])) {
            $this->db->where($option['where']);

            if (isset($option['from']) && !empty($option['from'])) {
                $update_data = isset($option['update_data']) ? $option['update_data'] : array();
                return $this->db->update($option['from'], $update_data);
            }
        }

        return false;
    }

    function custom_query($query)
    {
        return $this->db->query($query);
    }

    function delete($option)
    {
        if (isset($option['where']) && !empty($option['where'])) {
            $this->db->where($option['where']);

            if (isset($option['from']) && !empty($option['from'])) {
                return $this->db->delete($option['from']);
            }
        }

        return false;
    }

    function insert($option)
    {
        if ((isset($option['from']) && !empty($option['from'])) && (isset($option['insert_data']) && !empty($option['insert_data']))) {
            return $this->db->insert($option['from'], $option['insert_data']);
        }

        return false;
    }

    function count($option)
    {

        $this->db->select('*');
        $this->db->from($option['from']);

        if (isset($option['where']) && !empty($option['where'])) {
            $this->db->where($option['where']);
        }

        if (isset($option['between']) && isset($option['between']['key'])) {
            $between_key = $option['between']['key'];
            $between_min = isset($option['between']['min']) ? $option['between']['min'] : 0;
            $between_max = isset($option['between']['max']) ? $option['between']['max'] : 0;
            $this->db->where("$between_key BETWEEN '$between_min' AND '$between_max'");
        }

        $info = $this->db->get();
        return $info->num_rows();
    }

    // [/COMMON METHOD]
}
