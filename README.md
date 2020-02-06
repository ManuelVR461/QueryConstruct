# QueryConstruct
Constructor Basico de Querys Basado en la misma metodologia de CodeIgniter para PDO

## Aplicando:

$db_construc = new DB_model;

> $query = $db_construc->DB_select("p.descripcion as perfil")
>                      ->DB_from("usuarios u")
>                      ->DB_join('perfiles p','t.idperfil = p.id','INNER')
>                      ->DB_join('accesos a','t.idaccesos = a.id','INNER')
>                      ->DB_where('p.id',1)
>                      ->DB_where('u.id','T09','AND')
>                      ->DB_orderby('p.id,p.descripcion','DESC');
                     

## Estructura del Objeto Creado:

DB_model Object
(
    [db_select:protected] => Array
        (
            [0] => p.descripcion as perfil
        )

    [db_from:protected] => Array
        (
            [0] => usuarios u
        )

    [db_where:protected] => Array
        (
            [0] => WHERE p.id=:p.id
            [1] => AND u.id=:u.id
        )

    [db_join:protected] => Array
        (
            [0] => INNER JOIN perfiles p ON t.idperfil=p.id
            [1] => INNER JOIN accesos a ON t.idaccesos=a.id
        )

    [db_groupby:protected] => Array
        (
        )

    [db_orderby:protected] => Array
        (
            [0] => Array
                (
                    [field] => p.id,p.descripcion
                    [direction] =>  DESC
                )

        )

    [db_limit:protected] => 
    [db_offset:protected] => 
)

## Resultado:

SELECT
p.descripcion as perfil
FROM usuarios u
INNER JOIN perfiles p ON t.idperfil=p.id
INNER JOIN accesos a ON t.idaccesos=a.id
WHERE p.id=:p.id AND u.id=:u.id
ORDER BY p.id,p.descripcion DESC

Notas:
- Por Ahora solo tengo el metodo Select y las partes basicas...
