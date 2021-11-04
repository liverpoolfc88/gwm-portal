<?php

namespace App\Http\Controllers;

use App\II001File;
use App\II002File;
use App\ProducedVin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class II001FileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $page = $request->input('pagination')['page'];
        $itemsPerPage = $request->input('pagination')['itemsPerPage'];
        $user = auth('api')->user();

        if ( $user->hasRole(['superadmin', 'admin']) ){
            return II001File::latest()
                ->paginate($itemsPerPage == '-1' ? 1000 : $itemsPerPage, ['*'], 'page name', $page);
        }else{
            return II001File::latest()
                ->paginate($itemsPerPage == '-1' ? 1000 : $itemsPerPage, ['*'], 'page name', $page);


        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\II001File  $iI001File
     * @return \Illuminate\Http\Response
     */
    public function show(II001File $iI001File)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\II001File  $iI001File
     * @return \Illuminate\Http\Response
     */
    public function edit(II001File $iI001File)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\II001File  $iI001File
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, II001File $iI001File)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\II001File  $iI001File
     * @return \Illuminate\Http\Response
     */
    public function destroy(II001File $iI001File)
    {
        //
    }

    public function filecreate($filetype)
    {
        switch($filetype)
        {
            case 'II001': 
                {
                    $cars = ProducedVin::where('ii001_fname', '=', null)->get();
                    break;
                }
            case 'II002': 
                {
                    $cars = ProducedVin::where('ii002_fname', '=', null)->get();
                    break;
                }
        }
        if (count($cars) > 0 and $filetype ){
            switch($filetype){
                case 'II001': 
                    { 
                        $result = $this->filecontent_ii001($filetype, $cars);
                        $filename =  $result['filename'];
                        $filecontent =  $result['content'];
                        $recordcount =  $result['count'];
                        
                        // write to db
                        $data = II001File::Create([
                            'filename' => $filename,
                            'recordcount' => $recordcount,
                            'status' => '0'
                        ]);

                        // update *_fname field 
                        foreach ($cars as $car) {
                            $ProducedVin = ProducedVin::findOrFail($car->id);
                            $ProducedVin->ii001_fname = $filename;
                            $ProducedVin->save();
                        }
                        break;
                    }
                case 'II002': 
                    {
                        $result = $this->filecontent_ii002($filetype, $cars);
                        $filename =  $result['filename'];
                        $filecontent =  $result['content'];
                        $recordcount =  $result['count'];
                        
                        // write to db
                        $data = II002File::Create([
                            'filename' => $filename,
                            'recordcount' => $recordcount,
                            'status' => '0'
                        ]);
                        // update *_fname field 
                        foreach ($cars as $car) {
                            $ProducedVin = ProducedVin::findOrFail($car->id);
                            $ProducedVin->ii002_fname = $filename;
                            $ProducedVin->save();
                        }
                        break;
                    }
            }
            //Storage::disk('sftp')->put('MFTP00740build/'.$filenamewithextension, fopen($request->file('file'), 'r+'), 'public');
            Storage::disk('local')->put($filetype.'/'.$filename, $filecontent);
            //return $filename;


            return ['status' => '1', 'data' => $data, 'message' => $filetype.' file successfully created'];
        }else{
            return 
                    ['status' => '0', 'data'=> '', 'message' => 'No vin data for create file'.$filetype];
        }
    }

    public function filecontent_ii001($filetype, $cars)
    {
        $result = $this->filename($filetype);
        $filename = $result['filename'];
        $process_date = $result['process_date']; 
        $process_datetime = $result['process_datetime'];

        $header = 'H|'.$process_datetime.'|'.$filename."\n";
        $content = $header;

        $recordcount = 0;
        foreach ($cars as $car) {
            $detail = 'D|GWMUZDAT|WT00|'.$car->vin_gm.'||9J20|'.$process_date.'|||'.$car->model_year.'|A|L|KM|'.$car->full_option.'|'.$car->engine.'|||||||||||||0|'.$car->model_code.'||9J20'."\n";
            $content .= $detail;
            unset($detail);
            $recordcount++;
        }

        $footer = 'T|'.$process_datetime.'|'.$filename.'|'.$recordcount;
        $content .= $footer;
        return ['filename' => $filename, 'content' => $content, 'count' => $recordcount];
    }

    public function filecontent_ii002($filetype, $cars)
    {
        $result = $this->filename($filetype);
        $filename = $result['filename'];
        $process_date = $result['process_date']; 
        $process_datetime = $result['process_datetime'];

        $header = 'H|'.$process_datetime.'|'.$filename."\n";
        $content = $header;

        $recordcount = 0;
        foreach ($cars as $car) {
            $detail = 'D|GWMUZDAT|WT10|'.$car->vin_gm.'|||'.$car->to_dealer.'||'.$process_date.'|||||||'.'0|9J20|'.$car->to_dealer."\n";
            $content .= $detail;
            unset($detail);
            $recordcount++;
        }

        $footer = 'T|'.$process_datetime.'|'.$filename.'|'.$recordcount;
        $content .= $footer;
        return ['filename' => $filename, 'content' => $content, 'count' => $recordcount];
    }

    public function filename($filetype)
    {
        $process_datetime = date('YmdHis');
        $process_date = date('Ymd');
        $filename = $filetype.'_GWM_9J20_WEB_'.$process_datetime.'.txt';
        return ['filename' => $filename, 'process_date' => $process_date, 'process_datetime' => $process_datetime];
    }

    public function filesend($filetype)
    {
        switch($filetype)
        {
            case 'II001': 
                {
                    // sending II001 files to GWM server
                    $ii001_files = II001File::where('status', '=', '0')->get();
                    if (count($ii001_files) > 0) {
                        foreach ($ii001_files as $file) {
                            Storage::disk('sftp')->put('MFTP00740build/'.'test_'.$file->filename, Storage::disk('local')->get('II001/'.$file->filename));        
                            II001File::where('id',$file->id)->update(['status' => '1']);
                        }
                    }else{
                        return ['status' => '0', 'message' => $filetype.' files doesn`t have for sending.'];
                    }
                    
                    break;
                }
            case 'II002': 
                {
                    // sending II002 files to GWM server
                    $ii002_files = II002File::where('status', '=', '0')->get();
                    if (count($ii002_files) > 0) {
                        foreach ($ii002_files as $file) {
                            Storage::disk('sftp')->put('MFTP00740invoice/'.'test_'.$file->filename, Storage::disk('local')->get('II002/'.$file->filename));        
                            II002File::where('id',$file->id)->update(['status' => '1']);
                        }
                    }else{
                        return ['status' => '0', 'message' => $filetype.' files have doesn`t for sending.'];
                    }

                    break;
                }
        }
        return ['status' => '1', 'message' => $filetype.' files are successfully sent.'];
    }


}
