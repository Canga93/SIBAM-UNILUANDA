<?php
class AdvancedSearch {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Buscar monografias com filtros avançados
    public function search_monografias($filters = [], $page = 1, $limit = 10) {
        $query = "SELECT m.*, u.nome as autor 
                  FROM monografias m 
                  LEFT JOIN usuarios u ON m.autor_id = u.id 
                  WHERE m.status = 'aprovado'";
        
        $params = [];
        $conditions = [];
        
        // Filtros
        if (!empty($filters['search'])) {
            $conditions[] = "(m.titulo LIKE :search OR m.resumo LIKE :search OR m.palavras_chave LIKE :search OR u.nome LIKE :search)";
            $params[':search'] = "%{$filters['search']}%";
        }
        
        if (!empty($filters['area'])) {
            $conditions[] = "m.area = :area";
            $params[':area'] = $filters['area'];
        }
        
        if (!empty($filters['autor'])) {
            $conditions[] = "u.nome LIKE :autor";
            $params[':autor'] = "%{$filters['autor']}%";
        }
        
        if (!empty($filters['ano'])) {
            $conditions[] = "YEAR(m.data_publicacao) = :ano";
            $params[':ano'] = $filters['ano'];
        }
        
        if (!empty($filters['orientador'])) {
            $conditions[] = "m.orientador LIKE :orientador";
            $params[':orientador'] = "%{$filters['orientador']}%";
        }
        
        if (!empty($filters['palavras_chave'])) {
            $keywords = explode(',', $filters['palavras_chave']);
            $keywordConditions = [];
            
            foreach ($keywords as $index => $keyword) {
                $param = ":keyword_$index";
                $keywordConditions[] = "m.palavras_chave LIKE $param";
                $params[$param] = "%" . trim($keyword) . "%";
            }
            
            $conditions[] = "(" . implode(' OR ', $keywordConditions) . ")";
        }
        
        // Adicionar condições à query
        if (!empty($conditions)) {
            $query .= " AND " . implode(' AND ', $conditions);
        }
        
        // Ordenação
        $order_by = 'm.data_publicacao DESC';
        if (!empty($filters['ordenar'])) {
            switch ($filters['ordenar']) {
                case 'titulo':
                    $order_by = 'm.titulo ASC';
                    break;
                case 'recente':
                    $order_by = 'm.data_publicacao DESC';
                    break;
                case 'antigo':
                    $order_by = 'm.data_publicacao ASC';
                    break;
                case 'autor':
                    $order_by = 'u.nome ASC';
                    break;
            }
        }
        
        $query .= " ORDER BY $order_by";
        
        // Paginação
        $offset = ($page - 1) * $limit;
        $query .= " LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;
        
        // Executar query
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            if ($key === ':limit' || $key === ':offset') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
        
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Contar total
        $countQuery = "SELECT COUNT(*) as total 
                       FROM monografias m 
                       LEFT JOIN usuarios u ON m.autor_id = u.id 
                       WHERE m.status = 'aprovado'";
        
        if (!empty($conditions)) {
            $countQuery .= " AND " . implode(' AND ', $conditions);
        }
        
        $countStmt = $this->conn->prepare($countQuery);
        
        // Remover parâmetros de paginação
        $countParams = array_filter($params, function($key) {
            return $key !== ':limit' && $key !== ':offset';
        }, ARRAY_FILTER_USE_KEY);
        
        foreach ($countParams as $key => $value) {
            $countStmt->bindValue($key, $value);
        }
        
        $countStmt->execute();
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        return [
            'results' => $results,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ];
    }
    
    // Obter áreas disponíveis
    public function get_areas() {
        $query = "SELECT DISTINCT area FROM monografias WHERE area IS NOT NULL AND status = 'aprovado' ORDER BY area";
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    // Obter anos disponíveis
    public function get_anos() {
        $query = "SELECT DISTINCT YEAR(data_publicacao) as ano 
                  FROM monografias 
                  WHERE data_publicacao IS NOT NULL AND status = 'aprovado' 
                  ORDER BY ano DESC";
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    // Obter orientadores disponíveis
    public function get_orientadores() {
        $query = "SELECT DISTINCT orientador 
                  FROM monografias 
                  WHERE orientador IS NOT NULL AND orientador != '' AND status = 'aprovado' 
                  ORDER BY orientador";
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
?>