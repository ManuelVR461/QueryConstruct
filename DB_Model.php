<?php
include "Config.php";

class DB_model extends Config {
    /**
     * Campo de Conexion de DB
     */
    protected $cnx;
    protected $rows = array();
    protected $data_set = array();
    protected $data_where = array();
    protected $data_having = array();
    /**
     * Campos para construccion de consultas
     */
    protected $db_select= array();
    protected $db_from= array();
    protected $db_where= array();
    protected $db_join= array();
    protected $db_groupby= array();
    protected $db_havingby= array();
    protected $db_orderby= array();
    protected $db_limit= FALSE;
    protected $db_offset= FALSE;
    protected $db_insert= array();
    protected $db_update= array();
    protected $db_from_join= array();

    public function __construct(){
        parent::__construct();
        $this->cnx = $this->conexion();
    }

    protected function conexion(){
        try {
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => FALSE,
            ];
            return new PDO(parent::SGBD,parent::USER,parent::PASS,$options);
        } catch (PDOException $e) {
            echo "Error: ".$e;
            die();
        }
    }

    /** Extrae las llaves de un arreglo como una string separado por coma.
	 * @param Array $data		Arreglo de Datos.
	 * @return String        Cadena separada por coma
     */
    public function DB_getKeysArray($data){
        return implode(',',array_keys($data));
    }

    /** Extrae las llaves de un arreglo como una string separado por coma y : para sentencias preparadas.
	* @param Array $data		Arreglo de Datos.
	* @return String        Cadena separada por coma y dos puntos
    */
    public function DB_getKeysArrayPDO($data){
        $campos = implode(',',array_keys($data));
        $campos = str_replace(",",",:",$campos);
        return ":".$campos;
    }

    /** Estrae las llaves de un arreglo formateandola en llave=:llave para actualizaciones
    * con Sentencias preparadas
    * @param Array $data        Arreglo de Datos.
    * @return string Cadena con llave=:llave
    */
    public function DB_WhereDataPDO($data, $operador){
        foreach ($data as $key => $value) {
            $subkey = explode('.',$key);
            if(count($subkey)>1){
                $part[]=$key."$operador:".$subkey[1];
            }else{
                $part[]=$key."$operador:".$subkey[0];
            }
        }

        return implode(',',array_values($part));
    }

    /** actualiza los datos del arreglo con :llave para sentencias preparadas.
	* @param Array $data		Arreglo de Datos.
	* @return Array        Arreglo con :llaves
    */
    public function _getFormatDataPDO($data,$pre=''){
        foreach ($data as $key => $value) {
            $subkey = explode('.',$key);
            if(count($subkey)>1){
                $dataout[":".$pre.$subkey[1]]=$value;
            }else{
                $dataout[":".$pre.$subkey[0]]=$value;
            }
        }
        return $dataout;
    }

    /**
    * Recibe las columnas de los datos separados por coma a extraer para
    * construir la consulta select
    * @param string	$select	'campo1,campo2,campo3,...,campon'
    * @return Object $this,
	*/
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

    /**
    * Recibe los nombres de las tablas separados por coma a utilizar en las sql
    * @param string	$select	'tabla1,tabla2,tabla3,...,tablan'
    * @return Object $this,
	*/
    public function DB_from($from){
        foreach ((array) $from as $tabla){
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

    /**
    * Recibe recibe las uniones segun los datos requeridos para crear las sentencias con join
    * @param string	$tabla	'nombre tabla'
    * @param string	$consicion	'tabla1.campo=tabla.campo2'
    * @param string	$tipo	'TIPO DE UNION'
    * @return Object $this,
	*/
    public function DB_join($tabla, $condicion, $tipo = ''){
        $part_condicion='';

        if ($tipo !== ''){
            if (!in_array($tipo, array('LEFT', 'RIGHT', 'OUTER', 'INNER', 'LEFT OUTER', 'RIGHT OUTER'), TRUE)){
				$tipo = '';
			}else{
				$tipo .= ' ';
			}
        }
        $this->db_from_join[] = $tabla;
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

    /**
    * Recibe recibe las datos para la comparacion igual a.
    * @param string $campo  'nombre campo'
    * @param string $valor a comparar
    * @param string $operador operador de comparación '=' o '!=' o '<' o '>' o '<=' o '>=' o '!<' o '!>' (opcional)
    * @param string $union   'AND,OR' (opcional)
    * @return Object $this,
    */
    public function DB_where($campo, $valor, $operador = '=', $union=null){
        try {
            if($union && !$operador){
                throw new Exception("Parametros insuficientes");
            }
            if(!in_array($operador, ['=','!=','<','>','<=','>=','!<','!>'])){
                throw new Exception("Operador incorrecto");
            }
            if (!is_array($campo)){
                $campo = array($campo => $valor);
            }
            if($union){
                $this->db_where[] = $union.' '.$this->DB_WhereDataPDO($campo, $operador);
            }else{
                $this->db_where[] = "WHERE ".$this->DB_WhereDataPDO($campo, $operador);
            }
            $this->data_where = array_merge($this->data_where, $this->_getFormatDataPDO($campo));
            return $this;
        } catch (Exception  $e) {
            return $e->getMessage();
        }
    }

    public function _where(){
        return " ".implode(' ',$this->db_where);
    }

    /**
    * Recibe recibe las datos para la comparacion no igual a.
    * @param string $expresion  'nombre campo'
    * @param string $minimo a comparar
    * @param string $maximo a comparar
    * @param string $union   'AND,OR' (opcional)
    * @return Object $this,
    */
    public function DB_between($expresion, $minimo, $maximo, $union = FALSE){
        if((gettype($minimo) == gettype($maximo)) && ($minimo <= $maximo)){
            if($union){
                $this->db_where[] = $union.' :expresion BETWEEN (:minimo AND :maximo)';
            }else{
                $this->db_where[] = 'WHERE :expresion BETWEEN (:minimo AND :maximo)';
            }
            $campo = array(
                'expresion' => $expresion,
                'minimo'    => $minimo,
                'maximo'    => $maximo
            );
            $this->data_where = array_merge($this->data_where, $this->_getFormatDataPDO($campo));
        }
        return $this;
    }

    public function DB_notbetween($expresion, $minimo, $maximo, $union = FALSE){
        if((gettype($minimo) == gettype($maximo)) && ($minimo <= $maximo)){
            if($union){
                $this->db_where[] = $union.' :expresion NOT BETWEEN (:minimo AND :maximo)';
            }else{
                $this->db_where[] = 'WHERE :expresion NOT BETWEEN (:minimo AND :maximo)';
            }
            $campo = array(
                ':expresion' => $expresion,
                ':minimo'    => $minimo,
                ':maximo'    => $maximo
            );
            $this->data_where = array_merge($this->data_where, $this->_getFormatDataPDO($campo));
        }
        return $this;
    }

    /**
    * Recibe las columnas de los datos separados por coma a extraer para
    * construir la consulta Soluciones Algebraicas
    * @param string	$sum    'campo1,campo2,campo3,...,campon' si es un solo campo se muestra la suma de
    * dicha columna, cuando son multiples campos se muestra la solución algebraica de los campos
    * respecto al operador
    * @param string	$operador   '+' o '-' o '*' o '/' o '%'
    * @return Object $this,
	*/
    public function DB_algebra($suma, $operador = '+'){
        try {
            if(!in_array($operador, ['+','-','*','/','%'])){
                throw new Exception("Parametros insuficientes");
            }
            if (is_string($suma)){
                $sum = explode(',', $suma);
            }
            if(count($sum) > 1){
                $cadena = 'SUM('.implode(' '.$operador.' ', $sum).')';
                $this->db_select[] = $cadena;
            }else{
                foreach ($sum as $campo){
                    $val = trim($campo);
                    if ($val !== ''){
                        $this->db_select[] = 'SUM('.$campo.')';
                    }
                }
            }
            return $this;
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    /**
    * Recibe las columnas de los datos separados por coma a extraer para
    * construir la consulta CONCAT
    * @param string	$sum    'campo1,campo2,campo3,...,campon' si es un solo campo se muestra la suma de
    * dicha columna, cuando son multiples campos se muestra la solución algebraica de los campos
    * respecto al operador
    * @param string	$alias  opcional
    * @return Object $this,
	*/
    public function DB_concat($suma, $alias = null){
        $cadena = str_replace(',', '+', $sum);
        if($alias){
            $alias = ' AS '.$alias;
        }
        $this->db_select[] = $cadena.$alias;
        return $this;
    }

    /**
    * Recibe recibe las datos para la comparacion no igual a.
    * @param string $expresion  'nombre campo'
    * @param string $comparador a comparar
    * @param string $union   'AND,OR' (opcional)
    * @return Object $this,
    */
    public function DB_like($expresion, $comparador, $union = FALSE){
        if($union){
            $this->db_where[] = $union.' :expresion LIKE :comparador';
        }else{
            $this->db_where[] = 'WHERE :expresion LIKE :comparador';
        }
        $campo = array(
            'expresion'  => $expresion,
            'comparador' => "$comparador"
        );
        $this->data_where = array_merge($this->data_where, $this->_getFormatDataPDO($campo));
        return $this;
    }

    public function DB_notlike($expresion, $comparador, $union = FALSE){
        if($union){
            $this->db_where[] = $union.' :expresion NOT LIKE :comparador';
        }else{
            $this->db_where[] = 'WHERE :expresion NOT LIKE :comparador';
        }
        $campo = array(
            'expresion'  => $expresion,
            'comparador' => "$comparador"
        );
        $this->data_where = array_merge($this->data_where, $this->_getFormatDataPDO($campo));
        return $this;
    }

    /**
    * Recibe las columnas de los datos separados por coma a extraer para
    * construir la consulta AVG
    * @param string	$sum    'campo1,campo2,campo3,...,campon' si es un solo campo se muestra el promedio de
    * dicha columna, cuando son multiples campos se muestra la solución algebraica de los campos
    * respecto al operador
    * @param string	$operador   '+' o '-' o '*' o '/'
    * @return Object $this,
	*/
    public function DB_avg($suma, $operador = '+'){
        if (is_string($suma)){
			$sum = explode(',', $suma);
        }
        if(count($sum) > 1){
            $cadena = 'AVG('.implode(' '.$operador.' ', $sum).')';
            $this->db_select[] = $cadena;
        }else{
            foreach ($sum as $campo){
                $val = trim($campo);
                if ($val !== ''){
                    $this->db_select[] = 'AVG('.$campo.')';
                }
            }
        }
        return $this;
    }

    /**
    * Recibe las columnas de los datos separados por coma a extraer para
    * construir la consulta MIN
    * @param string	$sum    'campo1,campo2,campo3,...,campon' si es un solo campo se muestra el registro
    * dicha columna con menor valor, cuando son multiples campos se muestra el registro de menor valor
    * de la solución algebraica de los campos respecto al operador
    * @param string	$operador   '+' o '-' o '*' o '/'
    * @return Object $this,
	*/
    public function DB_min($suma, $operador = '+'){
        if (is_string($suma)){
			$sum = explode(',', $suma);
        }
        if(count($sum) > 1){
            $cadena = 'MIN('.implode(' '.$operador.' ', $sum).')';
            $this->db_select[] = $cadena;
        }else{
            foreach ($sum as $campo){
                $val = trim($campo);
                if ($val !== ''){
                    $this->db_select[] = 'MIN('.$campo.')';
                }
            }
        }
        return $this;
    }

    /**
    * Recibe las columnas de los datos separados por coma a extraer para
    * construir la consulta max
    * @param string	$sum    'campo1,campo2,campo3,...,campon' si es un solo campo se muestra el registro
    * dicha columna con mayor valor, cuando son multiples campos se muestra el registro de mayor valor
    * de la solución algebraica de los campos respecto al operador
    * @param string	$operador   '+' o '-' o '*' o '/'
    * @return Object $this,
	*/
    public function DB_max($suma, $operador = '+'){
        if (is_string($suma)){
			$sum = explode(',', $suma);
        }
        if(count($sum) > 1){
            $cadena = 'MAX('.implode(' '.$operador.' ', $sum).')';
            $this->db_select[] = $cadena;
        }else{
            foreach ($sum as $campo){
                $val = trim($campo);
                if ($val !== ''){
                    $this->db_select[] = 'MAX('.$campo.')';
                }
            }
        }
        return $this;
    }

    /**
    * Recibe recibe los campos separados por coma para agrupar el resultado
    * @param string $by  'campo1,campo2,campo3'
    * @return Object $this,
    */
    public function DB_groupby($by){
        if (is_string($by)){
            $by = explode(',', $by);
        }
        foreach ($by as $campo){
            $val = trim($campo);
            if ($val !== ''){
                $this->db_groupby[] = $campo;
            }
        }
        return $this;
    }

    public function _groupby(){
        return $sql = " GROUP BY ".implode(',',$this->db_groupby);
    }

    /**
    * Recibe recibe las datos para la comparacion igual a.
    * @param string $campo  'nombre campo'
    * @param string $valor a comparar
    * @param string $operador operador de comparación '=' o '!=' o '<' o '>' o '<=' o '>=' o '!<' o '!>' (opcional)
    * @param string $union   'AND,OR' (opcional)
    * @return Object $this,
    */
    public function DB_havingby($campo, $valor, $operador = '=', $union=null){
        try {
            if($union && !$operador){
                throw new Exception("Parametros insuficientes");
            }
            if(!in_array($operador, ['=','!=','<','>','<=','>=','!<','!>'])){
                throw new Exception("Operador incorrecto");
            }
            if (!is_array($campo)){
                $campo = array($campo => $valor);
            }
            if($union){
                $this->db_havingby[] = $union.' '.$this->DB_WhereDataPDO($campo, $operador);
            }else{
                $this->db_havingby[] = "HAVING BY ".$this->DB_WhereDataPDO($campo, $operador);
            }
            return $this;
        } catch (Exception  $e) {
            return $e->getMessage();
        }
    }

    public function _havingby(){
        return " ".implode(' ',$this->db_havingby);
    }

    /**
    * Recibe recibe los campos separados por coma para ordenar el resultado
    * @param string $orden  'campo1,campo2,campo3'
    * @param string $direccion  'ASC' or 'DESC'
    * @return Object $this,
    */
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

    public function _orderby(){
        return $sql = " ORDER BY ".$this->db_orderby[0]['field']
                     .$this->db_orderby[0]['direction'];
    }

    /**
    * Recibe recibe los valores del limite de los resultados
    * @param string $valor  'min 1'
    * @param string $hasta  'max n' (opcional)
    * @return Object $this,
    */
    public function DB_limit($valor, $hasta = null){
    	if($valor>0 && !$hasta){
            $limite = ' LIMIT '.$valor;
        }else{
            $limite = ' LIMIT '.$valor.','.$hasta;
        }
		$this->db_limit = $limite;
        return $this;
    }

    public function DB_insert($data=array()){
        $db_data=array();
        if(count($data)>0){
            $db_insert[] = array('field' => $this->DB_getKeysArray($data),
                                'values' => $this->DB_getKeysArrayPDO($data));
            $db_data[] = $this->_getFormatDataPDO($data);
        }else{
            $db_insert[] = array('field' => "[field1?,field2?,...]",
                               'values' => "[value1?,value2?,...]");
        }
        $this->data_set = array_merge($this->data_set, $db_data);
        $this->db_insert = array_merge($this->db_insert, $db_insert);
        return $this;
    }

    public function DB_update($data=array()){
        $db_data_set=array();
        if(count($data)>0){
            $db_update[] = array('values' => $this->DB_WhereDataPDO($data,'set_'));
            $db_data_set[] = $this->_getFormatDataPDO($data,'set_');
        }else{
            $db_update[] = array('values' => "[key1:value1?,key2:value2?,...]");
        }
        $this->data_set = array_merge($this->data_set, $db_data_set);
        $this->db_update = array_merge($this->db_update, $db_update);
        return $this;

    }

    public function _builder($sql){
        $sub=array();
        $subsql='';
        switch ($sql) {
            case 'SELECT':
                if (count($this->db_select) === 0){
                    $subsql = $sql .= ' * ';
                }else{
                    foreach ($this->db_select as $key => $campo){
                        $this->db_select[$key] = trim($campo);
                    }
                    $subsql =  $sql ." ".implode(', ', $this->db_select);
                }
                if (count($this->db_from) > 0){
                    $subsql .= " FROM ".implode(', ', $this->db_from);
                }
                if (count($this->db_join) > 0){
                    $subsql .= " ".implode(" ", $this->db_join);
                }

                if (count($this->db_where) > 0){
                    $subsql .= $this->_where();
                }
                if (count($this->db_groupby) > 0){
                    $subsql .= $this->_groupby();
                }
                if (count($this->db_havingby) > 0){
                    $subsql .= $this->_havingby();
                }
                if (count($this->db_orderby) > 0){
                    $subsql .= $this->_orderby();
                }
                if ($this->db_limit){
                    $subsql .= $this->db_limit;
                }
                $sql = (array) $subsql;
                break;
            case 'INSERT INTO':
                if (count($this->db_from) > 0){
                    $sql .= ' '.$this->db_from[0];
                }
                foreach ($this->db_insert as $key => $insert) {
                    $sub[] = $sql.' ('.$insert['field'].') VALUES ('.$insert['values'].');';
                }
                $sql = $sub;
                break;
            case 'UPDATE':
                if (count($this->db_from) > 0){
                    $sql .= ' '.$this->db_from[0];
                }
                foreach ($this->db_update as $key => $update) {
                        $subsql = $sql.' SET '.$update['values'];

                        if (count($this->db_join) > 0){
                            $subsql .= ' '.implode(" ", $this->db_join);
                        }

                        if (count($this->db_where) > 0){
                            $subsql .= str_replace("\n"," ",$this->_where());
                        }
                    $sub[] = $subsql.";";
                }
                $sql = $sub;
                break;
            case 'DELETE FROM':
                if (count($this->db_join) > 0){
                    $part = explode(' ',$sql);
                    if (count($this->db_from) > 0){
                        $tables = array_merge($this->db_from, $this->db_from_join);
                        $subsql = $part[0].' '.implode(", ", $tables).' '.$part[1].' '.$this->db_from[0];
                    }
                    if (count($this->db_join) > 0){
                        $subsql .= ' '.implode(' ', $this->db_join);
                    }
                    if (count($this->db_where) > 0){
                        $subsql .= str_replace("\n"," ",$this->_where());
                    }
                    $sub[] = $subsql;
                }else{
                    if (count($this->db_from) > 0){
                        foreach ($this->db_from as $key => $delete) {
                            $subsql = $sql.' '.$delete;
                            if (count($this->db_where) > 0){
                                $subsql .= str_replace("\n"," ",$this->_where());
                            }
                            $sub[] = $subsql;
                        }
                    }else{
                        return false;
                    }
                }
                $sql = $sub;
                break;
        default:
            # code...
            break;
        }
        return $sql;
    }

    protected function _reset_query(){
        $this->rows         = array();
        $this->data_set     = array();
        $this->data_where   = array();
        $this->data_having  = array();
        $this->db_select    = array();
        $this->db_from      = array();
        $this->db_where     = array();
        $this->db_join      = array();
        $this->db_groupby   = array();
        $this->db_havingby  = array();
        $this->db_orderby   = array();
        $this->db_limit     = FALSE;
        $this->db_offset    = FALSE;
        $this->db_insert    = array();
        $this->db_update    = array();
        $this->db_from_join = array();
    }

    protected function _execute($sql,$where=array(),$type){
        try {
            $row=array();
            $ti = microtime(true);
            $res = $this->cnx->prepare($sql);
            $res->execute($where);
            switch ($type) {
                case 'SELECT':
                    $row = $res->fetchALL(PDO::FETCH_ASSOC);
                    break;
                case 'INSERT':
                    $lastid = $this->cnx->lastInsertId();
                    $row=array("lastid"=>$lastid);
                    break;
                case 'UPDATE':
                    $rowcount = $res->rowCount();
                    $row=array("rowcount"=>$rowcount);
                    break;
                case 'DELETE':
                    $rowcount = $res->rowCount();
                    $row=array("rowcount"=>$rowcount);
                    break;
                case 'CALL':
                    $row = $res->fetchALL(PDO::FETCH_ASSOC);
                    break;
            }
            $res->closeCursor();
            $tf = microtime(true);
            return array('error'=>0,'message'=>'success!','data'=>$row);
        } catch (Exception $e) {
            return array('error'=>1,'message'=>$e->getMessage());
        }
    }

    public function DB_get($tabla = '', $limite = NULL, $hasta = NULL){
        if ($tabla !== '' && strtolower($tabla) !== 'not'){
            $this->DB_from($tabla);
        }
        if ( !empty($limite)){
            $this->DB_limit($limite, $hasta);
        }
        $sql = $this->_builder('SELECT');
        foreach ($sql as $key => $query) {
            if (strtolower($tabla) !== 'not'){
                $res = $this->_execute($query,$this->data_where,'SELECT');
            }else{
                $res = $sql;
            }
        }
        $this->_reset_query();
        return $res;
    }

    public function DB_set($tabla = ''){
        if ($tabla !== '' && strtolower($tabla) !== 'not'){
            $this->DB_from($tabla);
        }
        if (count($this->db_from) === 0){
            $this->DB_from('[table?]');
        }
        $sql = $this->_builder('INSERT INTO');
        foreach ($sql as $key => $query) {
            if (strtolower($tabla) !== 'not'){
                $res = $this->_execute($query,$this->data_set[$key],'INSERT');
            }else{
                $res = $sql;
            }
        }
        $this->_reset_query();
        return $res;
    }

    public function DB_put($tabla = ''){
        if ($tabla !== '' && strtolower($tabla) !== 'not'){
            $this->DB_from($tabla);
        }
        if (count($this->db_from) === 0){
            $this->DB_from('[table?]');
        }
        $sql = $this->_builder('UPDATE');
        $data_set = array_merge($this->data_set[0], $this->data_where);
        foreach ($sql as $key => $query) {
            if (strtolower($tabla) !== 'not'){
                $res = $this->_execute($query,$data_set,'UPDATE');
            }else{
                $res = $sql;
            }
        }
        $this->_reset_query();
        return $res;
    }


    public function DB_del($tabla = ''){
        if ($tabla !== '' && strtolower($tabla) !== 'not'){
            $this->DB_from($tabla);
        }
        if (count($this->db_from) === 0){
            $this->DB_from('[table?]');
        }
        $sql = $this->_builder('DELETE FROM');

        foreach ($sql as $key => $query) {
            if (strtolower($tabla) !== 'not'){
                $res = $this->_execute($query,$this->data_where,'DELETE');
            }else{
                $res = $sql;
            }
        }
        $this->_reset_query();
        return $res;
    }

    public function DB_call($nom_proc,$params){
        $sql = "CALL ".$nom_proc."(".$this->DB_getKeysArrayPDO($params).")";
        $res = $this->_execute($sql,$params,'CALL');
        $this->_reset_query();
        return $res;
    }

}

$db_construc1 = new DB_model;
$db_construc2 = new DB_model;
$db_construc3 = new DB_model;

$query1 = $db_construc1->DB_select()
                       ->DB_from("usuarios")
                       ->DB_where('id',2)
                       ->DB_where('usuario','manuel',"AND")
                       ->DB_orderby('id,nombres','ASC')
                       ->DB_groupby('nombres')
                       ->DB_get('not');


// $data1 = array("usuario"=>"JuanOrtiz",
//                "nombres"=>"Juan Ortiz",
//                "pwd"=>"1234",
//                "email"=>'juan123@gmail.com',
//                 "idperfil"=>1,
//                 "fecha"=>date("Y-m-d"),
//                 "accesos"=>"(100-500)");
// $data2 = array("usuario"=>"ReguloM",
//                 "nombres"=>"Regulo Marques",
//                 "pwd"=>"1234",
//                 "email"=>'regulom123@gmail.com',
//                 "idperfil"=>1,
//                 "fecha"=>date("Y-m-d"),
//                 "accesos"=>"(100-500)");

// $query1 = $db_construc1->DB_from('usuarios')->DB_insert($data1)->DB_insert($data2)->DB_set();


// $data1 = array("usuario"=>"MLuis",
//                "pwd"=>"1324",
//                "nombres"=>"Luis Miguel",
//                "email"=>'luis134@gmail.com',
//                "accesos"=>"(100-400-500)");

// $query1 = $db_construc1->DB_update($data1)
//                        ->DB_where('id',6)
//                        ->DB_where('usuario','mluis',"AND")
//                        ->DB_from("usuarios")->DB_put();

// $data1 = array("pwd"=>"132456");

// $query1 = $db_construc1->DB_update($data1)
//                        ->DB_where('id',8)
//                        ->DB_from("usuarios")->DB_put();


// $query1 = $db_construc1->DB_from('usuarios')
//                        ->DB_from('perfiles')
//                        ->DB_where('id',10)->DB_del();

// $query1 = $db_construc1->DB_from('usuarios u')
//                        ->DB_join('perfiles p','u.idperfil = p.id','INNER')
//                        ->DB_where('p.id',10)
//                        ->DB_where('u.usuario','JorgeG','AND')
//                        ->DB_del();

$query1 = $db_construc1->DB_from('usuarios')
                       ->DB_groupby('nombres')
                       ->DB_havingby('usuario','manuel')
                       ->DB_orderby('id,nombres','ASC')
                       ->DB_get('not');

echo "<pre>";
print_r($query1);
echo "</pre>";
// echo "<pre>";
// print_r($query2);
// echo "</pre>";
// echo "<pre>";
// print_r($query3);
// echo "</pre>";
