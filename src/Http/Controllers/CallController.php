<?php

namespace yousefkadah\FreePbx\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use yousefkadah\FreePbx\Ami\Actions\OriginateAction;
use yousefkadah\FreePbx\Ami\AmiManager;

class CallController extends Controller
{
    protected AmiManager $amiManager;

    public function __construct(AmiManager $amiManager)
    {
        $this->amiManager = $amiManager;
    }

    /**
     * Initiate a click-to-call.
     */
    public function clickToCall(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_extension' => 'required|string',
            'to_number' => 'required|string',
            'context' => 'nullable|string',
            'caller_id' => 'nullable|string',
        ]);

        try {
            $connection = $this->amiManager->getConnection();
            $originate = new OriginateAction($connection);

            $options = [];
            if (isset($validated['context'])) {
                $options['context'] = $validated['context'];
            }
            if (isset($validated['caller_id'])) {
                $options['caller_id'] = $validated['caller_id'];
            }

            $originate->call(
                $validated['from_extension'],
                $validated['to_number'],
                $options
            );

            return response()->json([
                'success' => true,
                'message' => 'Call initiated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
