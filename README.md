# QueryConstruct
Constructor Básico de Querys Basado en la misma metodología de CodeIgniter para PDO

Sólo intento crear un objeto que permita hacer el CRUD en cualquier aplicacion PHP de forma simple y básica para que todo aquel pueda hacer sus aportes


***Aplicando la solucion para sentencias SELECT:***

<pre>
$db_construc = new DB_model;
a) $query1 = $db_construc->DB_select('p.descripcion as perfil')
                     ->DB_from('usuarios u')
                     ->DB_join('perfiles p','t.idperfil = p.id','INNER')
                     ->DB_join('accesos a','t.idaccesos = a.id','INNER')
                     ->DB_where('p.id',1)
                     ->DB_where('u.id','T09','AND')
                     ->DB_orderby('p.id,p.descripcion','DESC')->DB_get();

ó

b) $query1 = $db_construc1->DB_select()
                       ->DB_from("usuarios")
                       ->DB_where('id',2)
                       ->DB_where('usuario','manuel',"AND")
                       ->DB_orderby('id,nombres','ASC')->DB_get();
</pre>                

## Resultado:
<pre>
Array
(
    [0] => SELECT p.descripcion as perfil FROM usuarios u INNER JOIN perfiles p ON t.idperfil=p.id 
           INNER JOIN accesos a ON t.idaccesos=a.id WHERE p.id=:p.id AND u.id=:u.id ORDER BY p.id,p.descripcion DESC;
)
</pre>

***Aplicando la solucion para sentencias INSERT:***

a) CON el uso de DB_from('TABLE') -> se toma como la tabla a INSERTAR los datos para lo cual DB_set() no se le establece ninguna cadena como nombre de la tabla. 

***Con una sola insert***

<pre>
$data = array("usuario"=>"manuel","cargo"=>"administrador","clave"=>'manuel123');
$query1 = $db_construc3->DB_from('usuarios')
                       ->DB_insert($data)
                       ->DB_set();
</pre>

## Resultado:
<pre>
Array
(
    [0] => INSERT INTO usuarios (usuario,cargo,clave) VALUES (:usuario,:cargo,:clave);
)
</pre>

***Con varias insert***

<pre>
$data1 = array("usuario"=>"manuel","cargo"=>"administrador","clave"=>'manuel123');
$data2 = array("usuario"=>"jose","cargo"=>"tecnico","clave"=>'123456');
$data3 = array("usuario"=>"Pedro","cargo"=>"Chofer","clave"=>'pedritoperez');
$query1 = $db_construc3->DB_from('usuarios')
                       ->DB_insert($data1)
                       ->DB_insert($data2)
                       ->DB_insert($data3)
                       ->DB_set();
</pre>

## Resultado:
<pre>
Array
(
    [0] => INSERT INTO usuarios (usuario,cargo,clave) VALUES (:usuario,:cargo,:clave);
    [1] => INSERT INTO usuarios (usuario,cargo,clave) VALUES (:usuario,:cargo,:clave);
    [2] => INSERT INTO usuarios (usuario,cargo,clave) VALUES (:usuario,:cargo,:clave);
)
</pre>

b) SIN el uso de DB_from('TABLE') -> se toma como la tabla a INSERTAR los datos DB_set() por lo que se le establece la cadena como nombre de la tabla. 

<pre>
$data = array("usuario"=>"manuel","cargo"=>"administrador","clave"=>'manuel123');
$query3 = $db_construc3->DB_insert($data)
                       ->DB_set('usuarios');
</pre>

arrojando los mismos resultados anteriores.



***Aplicando la solucion para sentencias UPDATE:***

a) CON el uso de DB_from('TABLE') -> se toma como la tabla a ACTUALIZAR los datos para lo cual DB_put() no se le establece ninguna cadena como nombre de la tabla. 

***Con una sola insert***

<pre>
$data = array("usuario"=>"manuel","cargo"=>"administrador","clave"=>'manuel123');
$query3 = $db_construc3->DB_from('usuarios')
                       ->DB_update($data)
                       ->DB_where('id',1)
                       ->DB_where('u.codigo','T09','AND')
                       ->DB_put();
</pre>

## Resultado:
<pre>
Array
(
    [0] => UPDATE usuarios SET usuario=:usuario,cargo=:cargo,clave=:clave WHERE id=:id AND u.codigo=:u.codigo;
)
</pre>

b) sin el uso de DB_from('TABLE') -> se toma como la tabla  a ACTUALIZAR los datos DB_put() por lo que se le establece la cadena como nombre de la tabla. 

<pre>
$data = array("usuario"=>"manuel","cargo"=>"administrador","clave"=>'manuel123');
$query3 = $db_construc3->DB_update($data1)
                       ->DB_where('id',1)
                       ->DB_put('usuarios');
</pre>

arrojando los mismos resultados anteriores.



***Aplicando la solucion para sentencias DELETE:***


a) CON el uso de DB_from('TABLE') -> se toma como la tabla a ELIMINAR los datos para lo cual DB_del() no se le establece ninguna cadena como nombre de la tabla. 

***Con una solo from***

<pre>
$query1 = $db_construc1->DB_from('usuarios')
                       ->DB_where('id',1)
                       ->DB_where('u.codigo','T09','AND')
                       ->DB_del();
</pre>

## Resultado:
<pre>
Array
(
    [0] => DELETE FROM usuarios WHERE id=:id AND u.id=:u.id;
)
</pre>


***Con varios from***

<pre>
$query1 = $db_construc1->DB_from('usuarios')
                       ->DB_from('perfiles')
                       ->DB_from('categorias')
                       ->DB_where('id',1)
                       ->DB_where('u.codigo','T09','AND')
                       ->DB_del();
</pre>

## Resultado:
<pre>
Array
(
    [0] => DELETE FROM usuarios WHERE id=:id AND u.codigo=:u.codigo;
    [1] => DELETE FROM perfiles WHERE id=:id AND u.codigo=:u.codigo;
    [2] => DELETE FROM categorias WHERE id=:id AND u.codigo=:u.codigo;
)
</pre>


***Con uso de inner join ***

<pre>
$query1 = $db_construc1->DB_from('usuarios')
                       ->DB_join('perfiles p','t.idperfil = p.id','INNER')
                       ->DB_where('id',1)
                       ->DB_where('u.id','T09','AND')
                       ->DB_del();
</pre>

## Resultado:
<pre>
Array
(
    [0] => DELETE usuarios, perfiles p FROM INNER JOIN perfiles p ON t.idperfil=p.id WHERE id=:id AND u.id=:u.id
)
</pre>

b) sin el uso de DB_from('TABLE') -> se toma como la tabla  a ELIMINAR los datos DB_del() por lo que se le establece la cadena como nombre de la tabla. 

<pre>
$query1 = $db_construc1->DB_where('id',1)
                       ->DB_where('u.codigo','T09','AND')
                       ->DB_del('usuarios');
</pre>

arrojando los mismos resultados anteriores.


Notas:
- Aun no se le han hecho las validaciones correspondientes, pero todo aquel que quiera aportar se le agradece.
- Aun la sentencia del con join esta en desarrollo ya que hay que evaluar los alias...


La Base de datos de ejemplo probada es:

1) CREATE DATA TABLE 'db_objectmvcx';

2) CREATE TABLE `perfiles` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `descripcion` VARCHAR(50) NOT NULL,
        `accesos_default` TEXT NOT NULL,
        `status` CHAR(1) NOT NULL DEFAULT 'A',
        PRIMARY KEY (`id`)
    )
    COMMENT='Perfiles de Usuarios'
    COLLATE='latin1_swedish_ci'
    ENGINE=InnoDB
    AUTO_INCREMENT=1;

    INSERT INTO perfiles (id, descripcion, accesos_default, status) VALUES (1, 'Master', '100-500', 'A');

3) CREATE TABLE `usuarios` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `usuario` VARCHAR(50) NOT NULL,
        `pwd` VARCHAR(150) NOT NULL,
        `nombres` VARCHAR(100) NOT NULL,
        `email` VARCHAR(50) NOT NULL,
        `idperfil` INT(11) NOT NULL,
        `fecha` DATE NOT NULL,
        `avatar` VARCHAR(50) NOT NULL DEFAULT 'avatar1.png',
        `accesos` TEXT NOT NULL,
        `status` CHAR(1) NOT NULL DEFAULT 'A',
        PRIMARY KEY (`id`),
        INDEX `idperfil` (`idperfil`)
    )
    COMMENT='Tabla Principal de Usuarios'
    COLLATE='latin1_swedish_ci'
    ENGINE=InnoDB
    AUTO_INCREMENT=1;
    
    INSERT INTO usuarios (id, usuario, pwd, nombres, email, idperfil, fecha, avatar, accesos, status) VALUES (1, 'admin', '1234', 'Master de Sistemas', 'ManuelVR461@gmail.com', 1, '2019-11-21', 'avatar1.png', '100-500', 'A');
