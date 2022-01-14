<?php

//Variaveis que recebem os valores inseridos no formulario html

$username = $_POST['Usuario']; //Recebe o usuario Digitado
$password = $_POST['Senha']; //Recebe a senha Digitada

/*
CN = Nome comum
OU = Unidade Organizacional
DC = Componente de Domínio
*/

//A variavel $ldap_dn Voce devera passar os componentes e o nome do dominio um exemplo seria ("CN=Usuarios,DC=google,DC=com",DC="br")
$ldap_dn = "CN=Exemplo,DC=exemplo,DC=com";

//A variavel $ldap_password ja e auto explicativa voce passara a senha de acesso do dominio 
$ldap_password = "Exemplo";

//ldap_con recebera o nome do host ou o IP do AD
$ldap_con = ldap_connect("192.168.0.0");


if($ldap_con){

    //Podera remover o "//" da linha abaixo para efetuar um "Debug" caso a conexao seja feita com sucesso sera mostrada a mensagem

    //echo " Conectado com sucesso ";

    //ldap_bind Autentica o ADM
    if(@ldap_bind($ldap_con, $ldap_dn, $ldap_password)){

        //Podera remover o "//" da linha abaixo para efetuar um "Debug" caso a conexao de administrador do AD tenha sido efetuada com sucesso

        //echo " Administrador Logado " . "<br />";
 

        //Aqui atribuimos o usuario do POST em outra variavel para melhor entendimento samaccountname ira filtrar o usuario e vera se existe ou nao no AD
        $samaccountname = $username;
        $filter = "(samaccountname=$samaccountname)";
        $dn="OU=Exemplo,DC=exemplo,DC=com";
        
        //Caso o usuario do AD nao seja localizado ele redirecionara para pagina incorreta.html informando que as credenciais estao incorretas
        $result = ldap_search($ldap_con, $dn, $filter) or exit ("<script language = 'javascript'> window.location = 'incorreta.html'; </script>");

        //A variavel $first Retorna o primeiro ID do "$result = (Filtro de usuarios)"
        $first = ldap_first_entry($ldap_con, $result);

        //Retorna o DN da "$first = (Primeiro resultado da pesquisa)" 
        $data = ldap_get_dn($ldap_con, $first);


        if ($ldap_con) {

            //ldap_bind Autentica o usuario
            $ldapbind = ldap_bind($ldap_con, $data, $password);
        

            //Disponibilizo aqui um filtro e uma formatacao caso queira listar todos usuarios do AD ou redirecionar para paginas diferentes dependendo do setor !!
            //!!!FINS DE ESTUDO OU IMPLEMENTACAO PARA TESTE DE FUNCIONALIDADE!!!

            if ($ldapbind) {

//-------------------------------------------------------------------------------------------

                //Codigo ja explicado porem aqui filtramos a categoria de usuarios no AD 
                $filterbd = "(objectcategory=user)";
                $dnldap="OU=Exemplo,DC=exemplo,DC=com";
                $resultsearch = ldap_search($ldap_con, $dnldap, $filterbd) or exit ("<script language = 'javascript'> window.location = 'incorreta.html'; </script>");

                //ldap_get_entries retorna todas as entradas em uma array multidimensional
                $ldapsearch = ldap_get_entries($ldap_con, $resultsearch);

                //Aqui contamos os elementos da array $ldapsearch "exemplo quantos usuarios temos no AD"
                $countdump = count($ldapsearch);


                //Aqui em baixo listamos toda a array de $ldapsearch caso desejamos listar todos usuarios do AD 

/*
                //LISTA TODOS OS USUARIOS DO AD
                echo '<pre>';
                var_dump($ldapsearch);
                echo '</pre>';


                //MOSTRA O TOTAL DE USUARIOS DO AD
                echo $countdump;
*/

//-------------------------------------------------------------------------------------------

//!! LEMBRANDO QUE ESSE E UM SISTEMA DE REDIRECIONAMENTO QUE E VARIAVEL OU SEJA DEPENDE DO RETORNO DA SUA ARRAY QUE SIRVA DE MODELO O CODIGO ABAIXO !! 

//Aqui pegamos o usuario filtrado de $data e extraimos variaveis

                //echo "<br />" . $data . "<br />";

                $array = explode('=',$data);

//Aqui removemos os espacos com rtrim e atribuimos os valores nas variaveis 

                //echo "<br />" . "Usuário Logado |" . "<br />";
                $Nome = rtrim($array[1], ' ,OU');
                //echo  $Nome . '<br />'. '<br />';
                

                //echo  "Setor |" . '<br />';
                $Setor = rtrim($array[2], ',OU');
                //echo  $Setor . '<br />' . '<br />';
                
                
                //echo  "Local |" . '<br />';
                $Local = rtrim($array[3], ',DC');
                //echo  $Local . '<br />' . '<br />';

//!! LEMBRANDO QUE ESSE E UM SISTEMA DE REDIRECIONAMENTO QUE E VARIAVEL OU SEJA DEPENDE DO RETORNO DA SUA ARRAY QUE SIRVA DE MODELO O CODIGO SUPRACITADO !! 
                
//Aqui ele pega os valores extraido do usuario e redireciona para pagina escolhida conforme o setor e abre uma SESSION para esse usuario


                if($Setor == 'Setor Do Administrador Do AD'){ //Exemplo redirecionaria para um dashbord de administrador

                    session_start();
                    $_SESSION['CONTDUMP'] = $countdump; //ja envia a quantidade de usuarios no AD via SESSION
                    $_SESSION['NOME'] = $Nome;
                    $_SESSION['SETOR'] = $Setor;
                    $_SESSION['PAINEL'] = "2";

                    echo "<script language = 'javascript'> window.location = './Admin'; </script>";

                }else{

                    session_start();
                    $_SESSION['NOME'] = $Nome;
                    $_SESSION['SETOR'] = $Setor;
                    $_SESSION['PAINEL'] = "1";

                    echo "<script language = 'javascript'> window.location = './Cliente'; </script>";

                }

            } else {

                //echo "Credenciais Incorretas";
                echo "<script language = 'javascript'> window.location = 'incorreta.html'; </script>";
            }

            //Disponibilizo aqui um filtro e uma formatacao caso queira listar todos usuarios do AD ou redirecionar para paginas diferentes dependendo do setor !!
            //!!!FINS DE ESTUDO OU IMPLEMENTACAO PARA TESTE DE FUNCIONALIDADE!!!
         
         }
    }
}

?>