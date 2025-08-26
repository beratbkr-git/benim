<?php
class CustomerController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }



    public function createCustomerSegment($segment_name, $description, $criteria)
    {
        try {
            $this->db->beginTransaction();

            $segment_id = $this->db->query("
                INSERT INTO bt_musteri_segmentleri (segment_adi, aciklama, kriterler) 
                VALUES (?, ?, ?)
            ", [$segment_name, $description, json_encode($criteria)]);

            $this->updateSegmentMembers($segment_id, $criteria);

            $this->db->commit();
            return $segment_id;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    private function updateSegmentMembers($segment_id, $criteria)
    {
        $this->db->query("DELETE FROM bt_musteri_segment_iliskileri WHERE segment_id = ?", [$segment_id]);

        $where_conditions = [];
        $params = [];

        if (isset($criteria['min_order_count'])) {
            $where_conditions[] = "siparis_sayisi >= ?";
            $params[] = $criteria['min_order_count'];
        }

        if (isset($criteria['min_total_spent'])) {
            $where_conditions[] = "toplam_harcama >= ?";
            $params[] = $criteria['min_total_spent'];
        }

        if (isset($criteria['registration_date_from'])) {
            $where_conditions[] = "kayit_tarihi >= ?";
            $params[] = $criteria['registration_date_from'];
        }

        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

        $customers = $this->db->fetchAll("
            SELECT 
                m.id,
                COUNT(s.id) as siparis_sayisi,
                COALESCE(SUM(s.toplam_tutar), 0) as toplam_harcama,
                m.kayit_tarihi
            FROM bt_musteriler m
            LEFT JOIN bt_siparisler s ON m.id = s.musteri_id
            GROUP BY m.id
            $where_clause
        ", $params);

        foreach ($customers as $customer) {
            $this->db->query("
                INSERT INTO bt_musteri_segment_iliskileri (musteri_id, segment_id) 
                VALUES (?, ?)
            ", [$customer['id'], $segment_id]);
        }

        $member_count = count($customers);
        $this->db->query("UPDATE bt_musteri_segmentleri SET musteri_sayisi = ? WHERE id = ?", [$member_count, $segment_id]);
    }

    public function getCustomerOrderHistory($customer_id, $limit = 20)
    {
        return $this->db->fetchAll("
            SELECT 
                s.*,
                COUNT(sd.id) as urun_sayisi
            FROM bt_siparisler s
            LEFT JOIN bt_siparis_detaylari sd ON s.id = sd.siparis_id
            WHERE s.musteri_id = ?
            GROUP BY s.id
            ORDER BY s.siparis_tarihi DESC
            LIMIT ?
        ", [$customer_id, $limit]);
    }

    public function exportCustomerData($filters = [])
    {
        $where_conditions = ['1=1'];
        $params = [];

        if (!empty($filters['segment_id'])) {
            $where_conditions[] = "m.id IN (SELECT musteri_id FROM bt_musteri_segment_iliskileri WHERE segment_id = ?)";
            $params[] = $filters['segment_id'];
        }

        if (!empty($filters['registration_date_from'])) {
            $where_conditions[] = "m.kayit_tarihi >= ?";
            $params[] = $filters['registration_date_from'];
        }

        if (!empty($filters['registration_date_to'])) {
            $where_conditions[] = "m.kayit_tarihi <= ?";
            $params[] = $filters['registration_date_to'];
        }

        $where_clause = implode(' AND ', $where_conditions);

        return $this->db->fetchAll("
            SELECT 
                m.*,
                COUNT(s.id) as toplam_siparis,
                COALESCE(SUM(s.toplam_tutar), 0) as toplam_harcama,
                MAX(s.siparis_tarihi) as son_siparis_tarihi
            FROM bt_musteriler m
            LEFT JOIN bt_siparisler s ON m.id = s.musteri_id
            WHERE $where_clause
            GROUP BY m.id
            ORDER BY m.kayit_tarihi DESC
        ", $params);
    }
}
