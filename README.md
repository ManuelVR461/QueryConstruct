# QueryConstruct
Constructor Basico de Querys Basado en la misma metodologia de CodeIgniter para PDO

***Aplicando la solucion para sentencias SELECT:***
<pre>
$db_construc = new DB_model;
$query = $db_construc->DB_select('p.descripcion as perfil')
                     ->DB_from('usuarios u')
                     ->DB_join('perfiles p','t.idperfil = p.id','INNER')
                     ->DB_join('accesos a','t.idaccesos = a.id','INNER')
                     ->DB_where('p.id',1)
                     ->DB_where('u.id','T09','AND')
                     ->DB_orderby('p.id,p.descripcion','DESC')->DB_get();
</pre>                

## Resultado:
<pre>
SELECT
p.descripcion as perfil
FROM usuarios u
INNER JOIN perfiles p ON t.idperfil=p.id
INNER JOIN accesos a ON t.idaccesos=a.id
WHERE p.id=:p.id AND u.id=:u.id
ORDER BY p.id,p.descripcion DESC
</pre>

***Aplicando la solucion para sentencias INSERT:***

a) con el uso de DB_from('TABLE') -> se toma como la tabla a insertar los datos para lo cual DB_set() no se le establece ninguna cadena como nombre de la tabla. 

***Con una sola insert***
<pre>
$data = array("usuario"=>"manuel","cargo"=>"administrador","clave"=>'manuel123');
$query3 = $db_construc3->DB_from('usuarios')
                       ->DB_insert($data)
                       ->DB_set();
</pre>

## Resultado:
una cadena de texto sql;
<pre>
INSERT INTO usuarios (usuario,cargo,clave) VALUES (:usuario,:cargo,:clave);
</pre>

***Con varias insert***
<pre>
$data1 = array("usuario"=>"manuel","cargo"=>"administrador","clave"=>'manuel123');
$data2 = array("usuario"=>"jose","cargo"=>"tecnico","clave"=>'123456');
$data3 = array("usuario"=>"Pedro","cargo"=>"Chofer","clave"=>'pedritoperez');
$query3 = $db_construc3->DB_from('usuarios')
                       ->DB_insert($data1)
                       ->DB_insert($data2)
                       ->DB_insert($data3)
                       ->DB_set();
</pre>

## Resultado:
un arreglo de texto sql;
<pre>
Array
(
    [0] => INSERT INTO usuarios (usuario,cargo,clave) VALUES (:usuario,:cargo,:clave);
    [1] => INSERT INTO usuarios (usuario,cargo,clave) VALUES (:usuario,:cargo,:clave);
    [2] => INSERT INTO usuarios (usuario,cargo,clave) VALUES (:usuario,:cargo,:clave);
)
</pre>

b) sin el uso de DB_from('TABLE') -> se toma como la tabla a insertar los datos DB_set() por lo que se le establece la cadena como nombre de la tabla. 

<pre>
$data = array("usuario"=>"manuel","cargo"=>"administrador","clave"=>'manuel123');
$query3 = $db_construc3->DB_insert($data)
                       ->DB_set('usuarios');
</pre>

arrojando los mismos resultados anteriores.

Notas:
- Por Ahora solo tengo el metodo Select e Insert y las partes basicas...
- Aun no se le han hecho las validaciones correspondientes.
