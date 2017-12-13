<?php

namespace App\Http\Controllers;

use App\Classes\Helpers\DataFormatter;
use App\Classes\Helpers\DateHelper;
use App\Models\ComplaintType;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Http\Request;
use App\Models\Complaint;
use App\Models\Log;

class ComplaintController extends Controller
{
    public function index(Request $request)
    {
        $params = $request->all();

        $filters = $request->has('filters') ? $request->get('filters') : [];

        $query = Complaint::query();

        if (!empty($filters['beginDate'])) {
            $query->where('created_at', '>=', DateHelper::toSqlFormat($filters['beginDate']));
        }

        if (!empty($filters['endDate'])) {
            $query->where('created_at', '<=', DateHelper::toSqlFormat($filters['endDate']));
        }

        if (!empty($filters['complaintType'])) {
            $query->where('type', $filters['complaintType']);
        }

        if (isset($filters['orderByDeadlibe']) && $filters['orderByDeadline'] == 'true') {
            $query->orderBy('action_deadline', 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        if (isset($filters['isOpenComplaints'])) {
            if ($filters['isOpenComplaints'] == 'yes') {
                $query->where('completion_date', '=', '0000-00-00 00:00:00');
            } else {
                $query->where('completion_date', '!=', '0000-00-00 00:00:00');
            }
        }

        if (isset($filters['branchId']) && $filters['branchId'] != '') {
            $query->where('branch_id', $filters['branchId']);
        }

        if (!empty($filters['handlerFullname'])) {
            $query->where('handler_fullname', 'like', '%' . $filters['handlerFullname'] . '%');
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        return DataFormatter::formatQueryResult($query, $params);
    }

    public function getTypes()
    {
        return ComplaintType::get()->map(function (ComplaintType $type) {
            return ['label' => ucfirst($type->name), 'value' => $type->id];
        })->all();
    }

    public function getPriorities()
    {
        return DataFormatter::formatEnumValues(Complaint::getEnumValues('priority'));
    }

    public function create(Request $request)
    {
        return $this->createModel(Complaint::className(), ['data' => $request->all()], function (Complaint $model) {
            Log::write(Log::ACTION_CREATE, Log::MODULE_COMPLAINT, $model->id);
        }, ['saveRelations' => true]);
    }

    public function get(Request $request, $id)
    {
        $complaint = Complaint::findOrFail($id);
        return $complaint->asArray(null, true);
    }

    public function edit(Request $request, $id)
    {
        return $this->editModel(
            Complaint::className(), ['data' => $request->all()], $id, ['saveRelations' => true],
            function (Complaint $model) {
                Log::write(Log::ACTION_UPDATE, Log::MODULE_COMPLAINT, $model->id);
            }
        );
    }

    public function delete(Request $request)
    {
        if (!$request->has('ids')) {
            return response()->json('Ids was not set', 500);
        }

        $reason = $request->has('reason') ? $request->get('reason') : '';

        Log::write(Log::ACTION_DELETE, Log::MODULE_COMPLAINT, implode(', ', $request->get('ids')), $reason);
        Complaint::whereIn('id', $request->get('ids'))->delete();
    }
}