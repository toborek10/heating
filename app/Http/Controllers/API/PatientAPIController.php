<?php

namespace App\Http\Controllers\API;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

/**
 * @group Patients
 *
 * Manage patients of current logged in physiotherapist
 */
class PatientAPIController extends APIController
{
    /**
     * Validation rules for controller requests
     *
     * @var array
     */
    private $validationRules = [
        'first_name' => 'required|string|min:1|max:100',
        'last_name' => 'required|string|min:1|max:100',
        'pesel' => 'nullable|pesel',
        'born_date' => 'nullable|date',
        'gender' => 'nullable|in:male,female',
        'address_street' => 'nullable|string|min:1|max:100',
        'address_postcode' => 'nullable|string|min:1|max:100',
        'address_city' => 'nullable|string|min:1|max:100',
        'email' => 'nullable|email|min:1|max:100',
        'phone' => 'nullable|string|min:1|max:100',
        'contact_person_first_name' => 'nullable|string|min:1|max:100',
        'contact_person_last_name' => 'nullable|string|min:1|max:100',
        'contact_person_address_street' => 'nullable|string|min:1|max:100',
        'contact_person_address_postcode' => 'nullable|string|min:1|max:100',
        'contact_person_address_city' => 'nullable|string|min:1|max:100',
        'contact_person_email' => 'nullable|email|min:1|max:100',
        'contact_person_phone' => 'nullable|string|min:1|max:100'
    ];

    /**
     * List
     *
     * Get current physiotherapist patients list
     *
     * Available scopes (use as URL query params):
     * - query: filter by first and last name
     * - page: current listing page number
     * - paginate: determine if result is paginated or not (1 or 0)
     * - fields: choose fields to be returned, separated by commas
     *
     * @authenticated
     * @response {
     *     "success": true,
     *     "message": "Pacjenci zostali pobrani pomyślnie",
     *     "data": {
     *         "current_page": 1,
     *         "data": [
     *             {
     *                 "id": 1,
     *                 "physiotherapist_id": 1,
     *                 "first_name": "Jan",
     *                 "last_name": "Kowalski",
     *                 "pesel": "83040782934",
     *                 "born_date": "1983-04-07",
     *                 "gender": "male",
     *                 "address_street": "Prosta 12",
     *                 "address_postcode": "12-456",
     *                 "address_city": "Sosnowiec",
     *                 "email": "jan.kowalski@example.com",
     *                 "phone": "501501501",
     *                 "contact_person_first_name": "Anna",
     *                 "contact_person_last_name": "Kowalska",
     *                 "contact_person_address_street": "Prosta 12",
     *                 "contact_person_address_postcode": "Prosta 12",
     *                 "contact_person_address_city": "Sosnowiec",
     *                 "contact_person_email": "anna.kowalska@example.com",
     *                 "contact_person_phone": "502502502",
     *                 "created_at": "2018-10-13 15:50:56",
     *                 "updated_at": "2018-10-13 15:50:56"
     *             }
     *         ]
     *         "first_page_url": "http://edf.local/api/patients?page=1",
     *         "from": 1,
     *         "last_page": 1,
     *         "last_page_url": "http://edf.local/api/patients?page=1",
     *         "next_page_url": null,
     *         "path": "http://edf.local/api/patients",
     *         "per_page": 10,
     *         "prev_page_url": null,
     *         "to": 1,
     *         "total": 1
     *     }
     * }
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function list()
    {
        $patients = $patient = auth()->user()->patients();

        if (request()->input('fields')) {
            $fields = explode(',', request()->input('fields'));
            if (count(array_intersect($fields, Patient::$fields)) != count($fields)) {
                return $this->sendErrorResponse('Podane pola są nieprawidłowe');
            }
            $patients->select($fields);
        }

        if (request()->input('query')) {
            $patients->whereRaw('concat(first_name, " ", last_name) like "%' . request()->input('query') . '%" ');
        }

        if (request()->input('paginate')) {
            $patients = $patients->paginate(10)->appends(Input::except('page'));
        } else {
            $patients = $patients->get();
        }

        return $this->sendSuccessResponse('Pacjenci zostali pobrani pomyślnie', $patients);
    }


    /**
     * Store
     *
     * Store new patient for current physiotherapist
     *
     * @authenticated
     * @bodyParam first_name string required First name of patient
     * @bodyParam last_name string required Last name of patient
     * @bodyParam pesel string required PESEL of patient
     * @bodyParam born_date date required Born date of patient (format: Y-m-d)
     * @bodyParam gender string required Gender of patient (in: male, female)
     * @bodyParam address_street string required Address street of patient
     * @bodyParam address_postcode string required Address postcode of patient
     * @bodyParam address_city string required Address city of patient
     * @bodyParam email email required Email of patient
     * @bodyParam phone string required Phone of patient
     * @bodyParam contact_person_first_name string required First name of contact person
     * @bodyParam contact_person_last_name string required Last name of contact person
     * @bodyParam contact_person_address_street string required Address street of contact person
     * @bodyParam contact_person_address_postcode' string required Address postcode of contact person
     * @bodyParam contact_person_address_city string required Address city of contact person
     * @bodyParam contact_person_email email required Email of contact person
     * @bodyParam contact_person_phone string required Phone of contact person
     * @response {
     *     "success": true,
     *     "message": "Pacjent został utworzony pomyślnie",
     *     "data": {
     *         "id": 1,
     *         "physiotherapist_id": 1,
     *         "first_name": "Jan",
     *         "last_name": "Kowalski",
     *         "pesel": "83040782934",
     *         "born_date": "1983-04-07",
     *         "gender": "male",
     *         "address_street": "Prosta 12",
     *         "address_postcode": "12-456",
     *         "address_city": "Sosnowiec",
     *         "email": "jan.kowalski@example.com",
     *         "phone": "501501501",
     *         "contact_person_first_name": "Anna",
     *         "contact_person_last_name": "Kowalska",
     *         "contact_person_address_street": "Prosta 12",
     *         "contact_person_address_postcode": "Prosta 12",
     *         "contact_person_address_city": "Sosnowiec",
     *         "contact_person_email": "anna.kowalska@example.com",
     *         "contact_person_phone": "502502502",
     *         "created_at": "2018-10-13 15:50:56",
     *         "updated_at": "2018-10-13 15:50:56"
     *     }
     * }
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate($this->validationRules);

        $patient = auth()->user()->patients()->create($validatedData);

        return $this->sendSuccessResponse('Pacjent został utworzony pomyślnie', $patient);
    }


    /**
     * Show
     *
     * Get selected patient of current physiotherapist
     *
     * @authenticated
     * @response {
     *     "success": true,
     *     "message": "Pacjent został pobrany pomyślnie",
     *     "data": {
     *         "id": 1,
     *         "physiotherapist_id": 1,
     *         "first_name": "Jan",
     *         "last_name": "Kowalski",
     *         "pesel": "83040782934",
     *         "born_date": "1983-04-07",
     *         "gender": "male",
     *         "address_street": "Prosta 12",
     *         "address_postcode": "12-456",
     *         "address_city": "Sosnowiec",
     *         "email": "jan.kowalski@example.com",
     *         "phone": "501501501",
     *         "contact_person_first_name": "Anna",
     *         "contact_person_last_name": "Kowalska",
     *         "contact_person_address_street": "Prosta 12",
     *         "contact_person_address_postcode": "Prosta 12",
     *         "contact_person_address_city": "Sosnowiec",
     *         "contact_person_email": "anna.kowalska@example.com",
     *         "contact_person_phone": "502502502",
     *         "created_at": "2018-10-13 15:50:56",
     *         "updated_at": "2018-10-13 15:50:56"
     *     }
     * }
     *
     * @param  int  $patientId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($patientId)
    {
        $patient = auth()->user()->patients()->findOrFail($patientId);

        return $this->sendSuccessResponse('Pacjent został pobrany pomyślnie', $patient);
    }

    /**
     * Update
     *
     * Update selected patient of current physiotherapist
     *
     * @authenticated
     * @bodyParam first_name string required First name of patient
     * @bodyParam last_name string required Last name of patient
     * @bodyParam pesel string required PESEL of patient
     * @bodyParam born_date date required Born date of patient (format: Y-m-d)
     * @bodyParam gender string required Gender of patient (in: male, female)
     * @bodyParam address_street string required Address street of patient
     * @bodyParam address_postcode string required Address postcode of patient
     * @bodyParam address_city string required Address city of patient
     * @bodyParam email email required Email of patient
     * @bodyParam phone string required Phone of patient
     * @bodyParam contact_person_first_name string required First name of contact person
     * @bodyParam contact_person_last_name string required Last name of contact person
     * @bodyParam contact_person_address_street string required Address street of contact person
     * @bodyParam contact_person_address_postcode' string required Address postcode of contact person
     * @bodyParam contact_person_address_city string required Address city of contact person
     * @bodyParam contact_person_email email required Email of contact person
     * @bodyParam contact_person_phone string required Phone of contact person
     * @response {
     *     "success": true,
     *     "message": "Pacjent został zaktualizowany pomyślnie",
     *     "data": {
     *         "id": 1,
     *         "physiotherapist_id": 1,
     *         "first_name": "Jan",
     *         "last_name": "Kowalski",
     *         "pesel": "83040782934",
     *         "born_date": "1983-04-07",
     *         "gender": "male",
     *         "address_street": "Prosta 12",
     *         "address_postcode": "12-456",
     *         "address_city": "Sosnowiec",
     *         "email": "jan.kowalski@example.com",
     *         "phone": "501501501",
     *         "contact_person_first_name": "Anna",
     *         "contact_person_last_name": "Kowalska",
     *         "contact_person_address_street": "Prosta 12",
     *         "contact_person_address_postcode": "Prosta 12",
     *         "contact_person_address_city": "Sosnowiec",
     *         "contact_person_email": "anna.kowalska@example.com",
     *         "contact_person_phone": "502502502",
     *         "created_at": "2018-10-13 15:50:56",
     *         "updated_at": "2018-10-13 15:50:56"
     *     }
     * }
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $patientId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $patientId)
    {
        $validatedData = $request->validate($this->validationRules);

        $patient = auth()->user()->patients()->findOrFail($patientId);

        $patient->update($validatedData);

        return $this->sendSuccessResponse('Pacjent został zaktualizowany pomyślnie', $patient);
    }

    /**
     * Delete
     *
     * Delete selected patient of current physiotherapist
     *
     * @authenticated
     * @response {
     *     "success": true,
     *     "message": "Pacjent został usunięty pomyślnie",
     *     "data": {}
     * }
     *
     * @param  int $patientId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy($patientId)
    {
        $patient = auth()->user()->patients()->findOrFail($patientId);

        $patient->delete();

        return $this->sendSuccessResponse('Pacjent został usunięty pomyślnie');
    }
}