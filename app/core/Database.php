<?php
// app/core/Database.php

class Database
{
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $charset = 'utf8mb4';
    private $conn;
    private $stmt;

    public function __construct()
    {
        // config.php'deki global degiskenleri kullan
        global $host, $datab, $user, $pass;
        $this->host = $host;
        $this->db_name = $datab;
        $this->username = $user;
        $this->password = $pass;

        $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            // canli ortamda detayli hata mesaji gosterme
            error_log("Veritabani Baglanti Hatasi: " . $e->getMessage());
            die("Veritabanı bağlantısı kurulamadı. Lütfen yönetici ile iletişime geçin.");
        }
    }

    /**
     * Hazırlanmış sorguları çalıştırmak için genel bir metot.
     * @param string $sql Çalıştırılacak SQL sorgusu.
     * @param array $params Sorgudaki yer tutucular için parametreler.
     * @return PDOStatement
     */
    public function query($sql, $params = [])
    {
        $this->stmt = $this->conn->prepare($sql);
        $this->stmt->execute($params);
        return $this->stmt;
    }

    /**
     * Sorgudan dönen tüm sonuçları bir dizi olarak alır.
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function fetchAll($sql, $params = [])
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Sorgudan dönen tek bir satırı alır.
     * @param string $sql
     * @param array $params
     * @return mixed
     */
    public function fetch($sql, $params = [])
    {
        return $this->query($sql, $params)->fetch();
    }

    /**
     * Bir tabloya yeni bir kayıt ekler.
     * @param string $table Tablo adı.
     * @param array $data Eklenecek veri ['sutun_adi' => 'deger'].
     * @return int|false Eklenen kaydın ID'si veya hata durumunda false.
     */
    public function insert($table, $data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";

        if ($this->query($sql, $data)) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    /**
     * Bir tablodaki kaydı günceller.
     * @param string $table Tablo adı.
     * @param array $data Güncellenecek veri ['sutun_adi' => 'deger'].
     * @param string $where WHERE koşulu (örn: "id = :id").
     * @param array $where_params WHERE koşulundaki parametreler.
     * @return bool Başarı durumu.
     */
    public function update($table, $data, $where, $where_params = [])
    {
        $fields = '';
        foreach ($data as $key => $value) {
            $fields .= "{$key} = :{$key}, ";
        }
        $fields = rtrim($fields, ', ');
        $sql = "UPDATE {$table} SET {$fields} WHERE {$where}";

        $params = array_merge($data, $where_params);
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount() > -1; // Sorgu basariliysa her zaman true doner
    }

    /**
     * Bir tablodan kayıt siler.
     * @param string $table Tablo adı.
     * @param string $where WHERE koşulu (örn: "id = :id").
     * @param array $params WHERE koşulundaki parametreler.
     * @return bool Başarı durumu.
     */
    public function delete($table, $where, $params = [])
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount() > -1; // Sorgu basariliysa her zaman true doner
    }
}
