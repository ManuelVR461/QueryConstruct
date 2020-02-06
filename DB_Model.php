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


    public function DB_WhereDataPDO($data){
        foreach ($data as $key => $value) {
            $part[]=$key."=:".$key;
        }
        return implode(',',array_values($part));
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
        
        $db_orderby[] = array('field' => $orden, 'direction' => $direccion, 'escape' => FALSE);
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
        $res = $this->query($this->_compilar_select());
		$this->_reset_select();
		return $res;
    }

}
$db_construc = new DB_model;

$query = $db_construc->DB_select("p.descripcion as perfil")
                     ->DB_from("usuarios u")
                     ->DB_join('perfiles p','t.idperfil = p.id','INNER')
                     ->DB_join('accesos a','t.idaccesos = a.id','INNER')
                     ->DB_where('p.id',1)
                     ->DB_orderby('p.id,p.descripcion','DESC');

echo "<pre>";
print_r($query);
echo "</pre>";