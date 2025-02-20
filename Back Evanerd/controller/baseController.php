<?php
include_once "includes/maLibUtils.php";

DEFINE("API_NAME", "EVANERD API");
DEFINE("VERSION", "1.0");


function getAuthHeader() {
    return valider("HTTP_AUTHTOKEN" , "SERVER");
}

/**
 * Permet de récupèrer la chaine de requête envoyer par le client
 * @return array
 */
function getRequestParams() {
    parse_str($_SERVER['QUERY_STRING'], $query);
    return $query;
}

/**
 * Permet de récupèrer le type de requête (GET POST PUT PATCH DEL )
 */
function getRequestType() {
    return valider("REQUEST_METHOD" , "SERVER");
}

/**
 * Fonction qui permet de séparer la route de l'api
 * @return array
 */
function getUriSegments()
{
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri = explode('/', $uri);
    return $uri;
}
/**
 * Renvoie le code de la route afin de pouvoir effecuter les traitements
 * @param string $request le champs rêquete de la qs
 * @param string $type type de la rêquete
 * @param array &$idTabs passage d'un tableau par références 
 *               des id trouver dans la route
 * 
 * @return string
 */
function getAction($request, $type, &$idTabs) {
    if($request === false) return "NOT_A_ACTION";

    $action = "$type ";
    $requestSegments = explode("/", $request);
    foreach($requestSegments as $segment) {
        if(is_id($segment)) {
            $action .= "/ID";
            $idTabs[] = intval($segment);
        }
        else {
            if(!empty($segment)) $action .= "/$segment";
        }
    }
    return $action;
}

/**
 * Génére les informations à propos de l'api
 * (sorte d'entête de reponse de l'api)
 */
function genereAPIInfo() {
    return array("apiname" => API_NAME, "version" => VERSION);
}

/**
 * Permet d'envoyer une réponse api
 * @param mixed $data un tableau associatif
 * @param array $httpHeaders Tableau contenant des entêtes de reponse HTTP
 */
function sendResponse($data, $httpHeaders = array())
{
    header_remove('Set-Cookie');
    if (is_array($httpHeaders) && count($httpHeaders)) {
        foreach ($httpHeaders as $httpHeader) {
            header($httpHeader);
        }
    }
    header("Content-Type: application/json");
    echo json_encode($data);
    exit;
}

/**
 * Renvoie une réponse d'erreur avec le status passé en paramètre
 * @param string $message le message d'erreur
 * @param int $status le status HTTP 
 */
function sendError($message, $status) {
    $data = genereAPIInfo();
    $data["status"] = $status;
    $data["error"] = $message;
    $httpHeaders = [getStatusHeader($status)];

    sendResponse($data, $httpHeaders);

}

/**
 * Renvoie l'entête de reponse correspond au status passé en paramètre
 * @param int $status
 * @Note Par defaut cette fonction renvoie le status 200
 */
function getStatusHeader($status = 200) {
    switch($status) {
        case 200: return "HTTP/1.0 200 OK";
        case 201: return "HTTP/1.0 201 Created";
        case 202: return "HTTP/1.0 202 Accepted";
        case 204: return "HTTP/1.0 204 No Content";
        case 400: return "HTTP/1.0 400 Bad Request";
        case 401: return "HTTP/1.0 401 Unauthorized";
        case 403: return "HTTP/1.0 403 Forbidden";
        case 404: return "HTTP/1.0 404 Not Found";
        default: return "HTTP/1.0 200 OK";
            
    }
}

?>