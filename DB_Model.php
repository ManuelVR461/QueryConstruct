<?php

class DB_model {
    /**
     * Campos para construccion de consultas
     */
    protected $db_select= array();
    protected $db_from= array();
    protected $db_where= array();
    protected $db_join= array();
    protected $db_groupby= array();
    protected $db_orderby= array();
    protected $db_limit= FALSE;
    protected $db_offset= FALSE;
    protected $db_insert= array();

    public function DB_WhereDataPDO($data){
        foreach ($data as $key => $value) {
            $part[]=$key."=:".$key;
        }
        return implode(',',array_values($part));
    }

    public function getKeysArray($data){
        return implode(',',array_keys($data));
    }

    public function getKeysArrayPDO($data){
        $campos = implode(',',array_keys($data));
        $campos = str_replace(",",",:",$campos);
        return ":".$campos;
    }

    public function DB_select($select = '*'){
        if (is_string($select)){
			$select = explode(',', $select);
        }
        foreach ($select as $campo){
            $val = trim($campo);
            if ($val !== ''){
                $this->db_select[] = $campo;
            }
        }
        return $this;
    }

    public function DB_from($from){
        foreach ((array) $from as $tabla){
            //strpos — Encuentra la posición de la primera ocurrencia de un substring en un string
            if (strpos($tabla, ',') !== FALSE){
                foreach (explode(',', $tabla) as $t){
                    $t = trim($t);
                    $this->db_from[] = $t;
                }
            }else{
                $t = trim($tabla);
                $this->db_from[] = $t;
            }
        }
        return $this;
    }

    public function DB_join($tabla, $condicion, $tipo = ''){
        $part_condicion='';

        if ($tipo !== ''){
            if (!in_array($tipo, array('LEFT', 'RIGHT', 'OUTER', 'INNER', 'LEFT OUTER', 'RIGHT OUTER'), TRUE)){
				$tipo = '';
			}else{
				$tipo .= ' ';
			}
        }

        if(!is_array($condicion)){
            $part = explode("=",$condicion);
            if(count($part)>1){
                $part_condicion = ' ON '.trim($part[0]).'='.trim($part[1]);
                $this->db_join[] = $tipo.'JOIN '.$tabla.$part_condicion;
            }else{
                $this->db_join[] = '';
                return $this;
            }
        }else{
            $this->db_join[] = '';
        }
        return $this;
    }

    public function DB_where($campo, $valor = NULL,$union=null){

        if (!is_array($campo)){
			$campo = array($campo => $valor);
        }
        if($union){
            $this->db_where[] = $union.' '.$this->DB_WhereDataPDO($campo);
        }else{
            $this->db_where[] = "WHERE ".$this->DB_WhereDataPDO($campo);
        }
        return $this;
    }

    public function DB_group_by($by){
        if (is_string($by)){
			$by = explode(',', $by);
        }
        foreach ($by as $campo){
            $campo = trim($campo);

            if ($campo !== ''){
                $campo = array('field' => $campo, 'escape' => NULL);
                $this->db_groupby[] = $campo;
            }

        }
        return $this;
    }
    
    public function DB_orderby($orden='id', $direccion = 'ASC'){
        $direccion = strtoupper(trim($direccion));
        
        if ($direccion !== ''){
			$direccion = in_array($direccion, array('ASC', 'DESC'), TRUE) ? ' '.$direccion : '';
        }
        
		if (empty($orden)){
			return $this;
        }
        
        $db_orderby[] = array('field' => $orden, 'direction' => $direccion);
        $this->db_orderby = array_merge($this->db_orderby, $db_orderby);
        return $this;
    }

    public function DB_limit($valor, $hasta = null){
		if($valor>0 && !$hasta){
            $limite = 'LIMIT '.$valor;
        }else{
            $limite = 'LIMIT '.$valor.','.$hasta;
        }
		return $limite;
    }
    
    public function DB_get($tabla = '', $limite = NULL, $hasta = NULL){
        if ($tabla !== ''){			
			$this->DB_from($tabla);
        }
        if ( !empty($limite)){
			$this->DB_limit($limite, $hasta);
        }
        $sql = $this->_compiler('SELECT');
		#$this->_reset_query();
		return $sql;
    }

    public function DB_insert($data=array()){

        if (count($this->db_from) > 0){
            $db_insert[] = array('field' => $this->getKeysArray($data), 
                             'values' => $this->getKeysArrayPDO($data));
            $this->db_insert = array_merge($this->db_insert, $db_insert);
        }else{
            $db_insert[] = array('field' => "[field1?,field2?,...]", 
                             'values' => "[value1?,value2?,...]");
            $this->db_insert = array_merge($this->db_insert, $db_insert);
        }
        
        return $this;

    }

    public function DB_set($tabla = ''){
        if ($tabla !== ''){			
			$this->DB_from($tabla);
        }

        if (count($this->db_from) === 0){
            $this->DB_from('[table?]');
        }

        $sql = $this->_compiler('INSERT INTO');
        #$this->_reset_query();
		return $sql;
    }

    public function _compiler($sql){
        switch ($sql) {
            case 'SELECT':
                
                if (count($this->db_select) === 0){
                    $sql .= ' * ';
                }else{
                    foreach ($this->db_select as $key => $campo){
                        $this->db_select[$key] = trim($campo);
                    }
                    $sql .= "\n".implode(', ', $this->db_select);
                }
                
                if (count($this->db_from) > 0){
                    $sql .= "\nFROM ".implode(', ', $this->db_from);
                }

                if (count($this->db_join) > 0){
                    $sql .= "\n".implode("\n", $this->db_join);
                }

                if (count($this->db_where) > 0){
                    $sql .= $this->_where();
                }

                if (count($this->db_orderby) > 0){
                    $sql .= $this->_orderby();
                }

                if ($this->db_limit){
                    $sql .= $this->db_limit;
                }

                break;

                case 'INSERT INTO':
                    $sub=array();
                    if (count($this->db_from) > 0){
                        $sql .= ' '.$this->db_from[0];
                    }
                    if (count($this->db_insert) > 1){
                        foreach ($this->db_insert as $key => $insert) {
                            $sub[] = $sql.' ('.$insert['field'].') VALUES ('.$insert['values'].');';
                        }
                        $sql = $sub;
                    }else{
                        $sql = $sql.' ('.$this->db_insert[0]['field'].') VALUES ('.$this->db_insert[0]['values'].');';
                    }
                    break;
            default:
                # code...
                break;
            
        }
        return $sql;
       
    }

    public function _where(){
        return "\n".implode(' ',$this->db_where);
    }

    public function _orderby(){
        return $sql = "\nORDER BY ".$this->db_orderby[0]['field']
                     .$this->db_orderby[0]['direction'];
    }

}
$db_construc1 = new DB_model;
$db_construc2 = new DB_model;
$db_construc3 = new DB_model;

// $query1 = $db_construc1->DB_select("p.descripcion as perfil")
//                      ->DB_from("usuarios u")
//                      ->DB_join('perfiles p','t.idperfil = p.id','INNER')
//                      ->DB_join('accesos a','t.idaccesos = a.id','INNER')
//                      ->DB_where('p.id',1)
//                      ->DB_where('u.id','T09','AND')
//                      ->DB_orderby('p.id,p.descripcion','DESC');

// $query2 = $db_construc2->DB_select("p.descripcion as perfil")
//                      ->DB_from("usuarios u")
//                      ->DB_join('perfiles p','t.idperfil = p.id','INNER')
//                      ->DB_join('accesos a','t.idaccesos = a.id','INNER')
//                      ->DB_where('p.id',1)
//                      ->DB_where('u.id','T09','AND')
//                      ->DB_orderby('p.id,p.descripcion','DESC')
//                      ->DB_get();
// echo "<pre>";
// print_r($query1);
// echo "</pre>";
// echo "<pre>";
// print_r($query2);
// echo "</pre>";

$data1 = array("usuario"=>"manuel","cargo"=>"administrador","clave"=>'manuel123');
$data2 = array("usuario"=>"jose","cargo"=>"tecnico","clave"=>'123456');
$data3 = array("usuario"=>"Pedro","cargo"=>"Chofer","clave"=>'pedritoperez');


$query1 = $db_construc1->DB_insert($data1)
                       ->DB_insert($data2)
                       ->DB_insert($data3)
                       ->DB_set('usuarios');

$query2 = $db_construc2->DB_insert()->DB_set();

// $query3 = $db_construc3->DB_from('usuarios')
//                        ->DB_insert($data1)
//                        ->DB_insert($data2)
//                        ->DB_insert($data3)
//                        ->DB_set();

echo "<pre>";
print_r($query1);
echo "</pre>";
echo "<pre>";
print_r($query2);
echo "</pre>";
echo "<pre>";
// print_r($query3);/
echo "</pre>";
