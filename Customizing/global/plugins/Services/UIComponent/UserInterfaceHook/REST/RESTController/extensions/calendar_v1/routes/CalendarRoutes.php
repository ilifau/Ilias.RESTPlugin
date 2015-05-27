<?php
/**
 * ILIAS REST Plugin for the ILIAS LMS
 *
 * Authors: D.Schaefer and T. Hufschmidt <(schaefer|hufschmidt)@hrz.uni-marburg.de>
 * 2014-2015
 */
namespace RESTController\extensions\calendar_v1;

// This allows us to use shortcuts instead of full quantifier
use \RESTController\libs\RESTLib, \RESTController\libs\AuthLib, \RESTController\libs\TokenLib;
use \RESTController\libs\RESTRequest, \RESTController\libs\RESTResponse;


/**
 * Route definitions for the REST Calendar API
 */
$app->group('/v1', function () use ($app) {
    /**
     * Returns the calendar events of a user specified by its user_id.
     */
    $app->get('/cal/events/:id', '\RESTController\libs\AuthMiddleware::authenticate', function ($id) use ($app) {
        $env = $app->environment();
        $response = new RESTResponse($app);
        $authorizedUserId =  RESTLib::loginToUserId($env['user']);

        if ($authorizedUserId == $id || RESTLib::isAdmin($authorizedUserId)) { // only the user or the admin is allowed to access the data
            try {
                $model = new CalendarModel();
                $data = $model->getCalUpcomingEvents($id);
                $response->setMessage("Upcoming events for user " . $id . ".");
                $response->addData('items', $data);
            } catch (\Exception $e) {
                $response->setRESTCode("-15");
                $response->setMessage('Error: Could not retrieve any events for user '.$id.".");
            }
        } else {
            $response->setRESTCode("-13");
            $response->setMessage('User has no RBAC permissions to access the data.');
        }
        $response->toJSON();
    });

    /**
     * Returns the ICAL Url of the desktop calendar of a user specified by its user_id.
     */
    $app->get('/cal/icalurl/:id', '\RESTController\libs\AuthMiddleware::authenticate' , function ($id) use ($app) {
        $env = $app->environment();
        $response = new RESTResponse($app);
        $authorizedUserId =  RESTLib::loginToUserId($env['user']);
        if ($authorizedUserId == $id || RESTLib::isAdmin($authorizedUserId)) { // only the user or the admin is allowed to access the data
            try {
                $model = new CalendarModel();
                $data = $model->getIcalAdress($id);
                $response->setMessage("ICAL (ics) address for user " . $id . ".");
                $response->addData('icalurl', $data);
            } catch (\Exception $e) {
                $response->setRESTCode("-15");
                $response->setMessage('Error: Could not retrieve ICAL url for user '.$id.".");
            }
        } else {
            $response->setRESTCode("-13");
            $response->setMessage('User has no RBAC permissions to access the data.');
        }
        $response->toJSON();
    });
});