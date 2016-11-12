<?php

// Fonction de connexion au LDAP et de vérification de l'identité de l'utilisateur
function bindLdapUser($login, $password)
{
    try
    {
        // Connexion au serveur LDAP
        $conn=ldap_connect(LDAP_SERVER);

        if ($conn) {
            // On dit qu'on utilise LDAP V3, sinon la V2 par défaut est utilisé
            // et le bind ne passe pas.
            ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);

            // Authentification sur le serveur LDAP
            $ldapbind = ldap_bind($conn, "uid=" . $login . ",ou=users," . LDAP_BASE_DN, $password);

            // Vérification de l'authentification
            if ($ldapbind) {

                $filter="uid=$login";
                $fields = array("employeeNumber", "sn", "givenName", "mail", "userPassword");
                $search = ldap_search($conn, LDAP_BASE_DN, $filter, $fields);
                $info = ldap_get_entries($conn, $search);
                $nb_results = $info['count'];

                if( $nb_results != 1 )
                {
                    error_log("Error LDAP: not exactly 1 user!");
                    return null;
                }
                else
                {
                    if( !isset($info[0]['mail'][0]) )
                    {
                        error_log("Error LDAP: no mail for user!");
                        return null;
                    }
                    else
                    {
                        $firstname = isset( $info[0]['givenname'][0] ) ? $info[0]['givenname'][0] : 'unknown';
                        $lastname = isset( $info[0]['sn'][0] ) ? $info[0]['sn'][0] : 'unkonwn';
                        $id = isset( $info[0]['employeenumber'][0] ) ? $info[0]['employeenumber'][0] : null;
                        $mail = isset( $info[0]['mail'][0] ) ? $info[0]['mail'][0] : null;
                        return array($firstname, $lastname, $id, $mail)
                    }
                }
            }
            else {
                error_log("Error LDAP: Connection failed");
                return null;
            }
            ldap_close($conn);
       }
    }
    catch (Exception $e)
    {
        error_log("Error LDAP: " . $e);
        return null;
    }
}
