<?php

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Casa_Api
 *
 * This class provides methods to interact with the Casa API.
 */
class Casa_Api
{
    private string $token;
    private string $project_id;
    private string $domain;

    /**
     * Retrieves company information by name.
     *
     * @param string $name The name of the company to search for.
     *
     * @return stdClass Returns an object containing the company information.
     *                  - status: The status of the response (either "success" or "error").
     *                  - message: A message from the response, either an error message or the company details.
     *                  - result: An object containing the company details.
     *                    - name: The name of the company.
     *                    - corporate_id: The corporate ID of the company.
     *                    - address: An object containing the company's address details.
     *                      - address_row_1: The first line of the address.
     *                      - city: The city of the address.
     *                      - zip_code: The zip code of the address.
     *                      - email: The email address associated with the address.
     *
     * @throws RuntimeException if the retrieval of companies is unsuccessful or the provided name is empty.
     * @since 1.0.0
     */
    public static function get_company( $name ): stdClass
    {
        $response = new stdClass();
        $response->status = "error";
        $response->message = null;

        $companies = Casa_Api::handle( 'companies/?name=' . urlencode( trim( $name ) ) );
        if ( $companies->success == 'true' ) {
            $data = $companies->message;
            $message = esc_attr__( "We have found a company with the same name as you entered:", 'casa-courses' ) . "\n\n";

            if ( is_object( $data ) ) {
                if ( property_exists( $data, 'count' ) && $data->count > 0 ) {
                    $message .= $data->results[ 0 ]->name . "\n";
                    !empty( $data->results[ 0 ]->corporate_id ) ?? $message .= esc_attr__( "Corporate ID:", 'casa-courses' ) . $data->results[ 0 ]->corporate_id . "\n";
                    $address_count = count( $data->results[ 0 ]->addresses );

                    if ( $address_count > 0 ) {
                        $invoicing_address = array_filter( $data->results[ 0 ]->addresses, function ( $item ) {
                            return $item->type === 'invoicing';
                        } );
                        $address = $address_count === 1 ? $data->results[ 0 ]->addresses[ 0 ] : reset( $invoicing_address );

                        if ( !empty( $address ) && !empty( $address->address_row_1 ) ) {
                            $message .= esc_attr__( "Address:", 'casa-courses' ) . "\n";
                            $message .= $address->address_row_1 . ", " . $address->zip_code . " " . $address->city . "\n";

                            $hyd_address = new stdClass();
                            $hyd_address->address_row_1 = $address->address_row_1;
                            $hyd_address->city = $address->city;
                            $hyd_address->zip_code = $address->zip_code;
                            $hyd_address->email = $address->email;
                            $data->results[ 0 ]->address = $hyd_address;
                        }
                    }

                    $message .= "\n" . esc_attr__( "Do you want to use this company for your registration?", 'casa-courses' ) . "\n";

                    $response->message = $message;
                    $response->result = $data->results[ 0 ];
                    $response->status = 'success';
                }
            }
        }

        return $response;
    }

    /**
     * Handles API requests based on the specified request type.
     *
     * @param string $url The URL to send the request to.
     * @param string $type The request type (GET or POST).
     * @param string $payload The payload to send in the request body for POST requests.
     *
     * @return stdClass Returns an object containing the response data.
     *                  - success: true if the request was successful, false otherwise.
     *                  - status: The status code of the response.
     *                  - message: A message from the response, either an error message or the data.
     *
     * @throws RuntimeException if the request type is invalid.
     * @since 1.0.0
     */
    public static function handle( string $url, string $type = "GET", string $payload = '' ): stdClass
    {
        $api = new Casa_Api();

        if ( $type == "GET" ) {
            return $api->get( $url );
        }

        if ( $type == "POST" ) {
            return $api->post( $url, $payload );
        }

        throw new RuntimeException( esc_attr__( 'Error: Invalid Request', 'casa-courses' ) );
    }

    /**
     * Verifies the settings by making a GET request to the specified API endpoint.
     *
     * @param string $domain The domain to send the request to.
     * @param string $token The API token to use for authentication.
     * @param string $project_id The project ID to associate with the request.
     *
     * @return bool Returns true if the settings are verified successfully, false otherwise.
     *
     * @throws RuntimeException if the token is empty.
     * @since 1.0.0
     */
    public static function verify_settings( string $domain, string $token, string $project_id ): bool
    {
        $api = new Casa_Api();
        $api->domain = 'https://' . $domain;
        $api->token = $token;
        $api->project_id = $project_id;

        $result = $api->get( 'areas/' );

        return $result->success;
    }

    /**
     * Constructor method for the class.
     *
     * Initializes the object by retrieving the token, project ID, and domain from options.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->token = get_option( 'casa_courses_token' );
        $this->project_id = get_option( 'casa_courses_project_id' );
        $this->domain = "https://" . get_option( 'casa_courses_domain' );
    }

    /**
     * Retrieves data from a specified URL using a GET request.
     *
     * @param string $url The URL to send the GET request to.
     *
     * @return stdClass Returns an object containing the response data.
     *                  - success: true if the request was successful, false otherwise.
     *                  - status: The status code of the response.
     *                  - message: A message from the response, either an error message or the data.
     *
     * @throws RuntimeException if the token is empty.
     * @since 1.0.0
     */
    public function get( string $url ): stdClass
    {
        if ( empty( $this->token ) ) {
            throw new RuntimeException( esc_attr__( 'Error: Invalid Token', 'casa-courses' ) );
        }

        return $this->decode_response( wp_remote_get( $this->get_complete_url( $url ), array (
            'headers' => $this->get_headers(),
            'timeout' => 50
        ) ) );
    }

    /**
     * Sends a POST request to the specified URL with the given payload and returns the response.
     *
     * @param string $url The URL where the POST request will be sent.
     * @param string $payload An associative array containing the data to be sent in the request body.
     * @return stdClass Returns an object containing the response data.
     *                  - success: true if the request was successful, false otherwise.
     *                  - status: The status code of the response.
     *                  - message: A message from the response, either an error message or the data.
     * @throws RuntimeException If the access token is empty, indicating an invalid token.
     * @since 1.0.0
     */
    public function post( string $url, string $payload ): stdClass
    {
        if ( empty( $this->token ) ) {
            throw new RuntimeException( esc_attr__( 'Error: Invalid Token', 'casa-courses' ) );
        }

        return $this->decode_response( wp_remote_post( $this->get_complete_url( $url ), array (
            'headers' => $this->get_headers(),
            'timeout' => 50,
            'body'    => $payload
        ) ) );
    }

    /**
     * Decodes the response returned from a HTTP request.
     *
     * @param mixed $response The response returned from the HTTP request.
     * @return stdClass Returns an object containing the decoded response data.
     *                  - success: true if the request was successful, false otherwise.
     *                  - status: The status code of the response.
     *                  - message: A message from the response, either an error message or the data.
     * @since 1.0.0
     */
    private function decode_response( $response ): stdClass {
        $return_value = new stdClass();
        $return_value->success = false;
        $return_value->status = 500;

        if ( is_wp_error( $response ) ) {
            $return_value->message = $response->get_error_message();
            return $return_value;
        }
        $data = json_decode( wp_remote_retrieve_body( $response ) );

        if ( $response[ 'response' ][ 'code' ] == 200 || $response[ 'response' ][ 'code' ] == 201 ) {
            if ( is_null( $data ) ) {
                $return_value->message = esc_attr__( "Unable to decode returned JSON", 'casa-courses' );
                return $return_value;
            } else {
                $return_value->success = true;
            }
        }

        $return_value->status = $response[ 'response' ][ 'code' ];
        $return_value->message = $data;
        return $return_value;
    }

    /**
     * Returns the complete URL based on the given URL.
     *
     * @param string $url The URL to construct the complete URL from.
     *
     * @return string The complete URL.
     *
     * @since 1.0.0
     */
    private function get_complete_url( string $url ): string
    {
        if ( str_starts_with( $url, 'sectors/' ) || str_starts_with( $url, 'dietary-preferences/' ) || str_starts_with( $url, 'companies/' ) ) {
            return $this->domain . CASA_COURSES_API . $url;
        } else {
            return $this->domain . ( !empty( $this->project_id ) ? ( CASA_COURSES_PROJECT_API . $this->project_id . '/' ) : CASA_COURSES_API ) . $url;
        }
    }

    /**
     * Returns the headers for the API request.
     *
     * @return array An associative array containing the headers for the API request.
     *               The keys represent the header names, while the values represent the header values.
     *               The array includes the following headers:
     *               - Accept: The desired response format for the API request.
     *                 This header specifies that the client expects to receive a response in JSON format.
     *                 The value is "application/json".
     *               - Authorization: The authorization header for the API request.
     *                 This header includes the access token required to authenticate the request.
     *                 The value is "Bearer " followed by the access token obtained from the authorization process.
     *               - Content-Type: The content type header for the API request.
     *                 This header specifies that the client is sending the request body in JSON format.
     *                 The value is "application/json".
     *               - Accept-Language: The accept language header for the API request.
     *                 This header specifies the desired language for the response.
     *                 The value is the first two characters of the current locale using the substr() function.
     *                 Note: The function get_locale() is used to retrieve the current locale.
     * @since 1.0.0
     */
    private function get_headers(): array
    {
        return array (
            'Accept'          => 'application/json',
            'Authorization'   => 'Api-Key ' . $this->token,
            'Content-Type'    => 'application/json',
            'Accept-Language' => substr( get_locale(), 0, 2 )
        );
    }
}