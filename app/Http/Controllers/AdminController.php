<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests;
use App\Cakes;
use App\Orders;
use App\Settings;
use Illuminate\Support\Facades\Hash;
use App\OrderCakes;
    use App\User;
    use App\Emirates;
    use App\Timeslots;
    use App\Slider;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    public function home() {

        $orders = Orders::orderBy('id', 'desc')->paginate(20);
      return view('admin.dashboard')->with('orders',$orders);


    }

    public function viewCake(Request $request) {

        $cake = Cakes::where('id',$request->id)->first();
        $cake["shaped"] = "35";
        return view('admin.viewCake')->with('cake',$cake);

    }

    public function deleteCake(Request $request) {

        $cake = Cakes::where('id',$request->id)->first();
        Cakes::where('id',$request->id)->update([
          'status' => 0
        ]);

        return redirect('/viewCakes');

    }

    public function deleteSlider(Request $request) {

        $slider = Slider::where('id',$request->id)->first();

        Slider::where('id',$request->id)->update([
          'status' => 0
        ]);

        return redirect('/viewSliders');

    }

    public function viewSettings(Request $request) {

        $settings = Settings::first();
        $settings['shaped_charge'] = Cakes::where('is_shaped',1)->first()["amount"];
        return view('admin.viewSettings')->with('settings',$settings);

    }

    public function changeSettings(Request $request) {

      $this->validate($request, [
                      'photo_charge' => 'required',
                      'shaped_charge' => 'required',
                  ]);
                  Settings::where('id',0)->update([
                      'photo_charge' => $request['photo_charge']
                    ]);
                    Cakes::where('is_shaped',1)->update([
                        'amount' => $request['shaped_charge'],
                        'shaped_amount' => $request['shaped_charge']
                      ]);
        return redirect('/viewSettings');

    }
    public function editCake(Request $request) {


        $this->validate($request, [
                        'is_shaped' => 'required',
                        'cake_id' => 'required|exists:cakes,id',
                        'is_shaped_avail' => 'required',
                        'is_photo_avail' => 'required',
                        'name' => 'required',
                        'amount' => 'required',
                        'minimum_kg' => 'required'
                    ]);
        if($request['is_shaped'] == "1") {
          $request['amount'] = 35;
        }
        $id = $request['cake_id'];
        $oldName =  Cakes::where('id',$id)->first()["name"];
        if (Cakes::where('name',$request['name'])->count() > 0) {
          return Redirect::back()->withErrors(['Cake with this name already exist.']);
        }
        Cakes::where('id',$id)->update([
                                       'description' => $request['description'],
                                       'is_shaped' => $request['is_shaped'],
                                       'shaped_amount' => $request['amount'],
                                       'is_shaped_avail' => $request['is_shaped_avail'],
                                       'is_photo_avail' => $request['is_photo_avail'],
                                       'name' => $request['name'],
                                       'amount' => $request['amount'],
                                       'discount' => 0,
                                       'discount_type' => 0,
                                       'cake_type_id' => 1,
                                       'minimum_kg' => $request['minimum_kg'],
                                       'avail_from' => '10:00:00',
                                       'avail_to' => '21:00:00',
                                       'pic_count' => 1,
                                       'status' => 1,
                                       ]);

        $i = 0;
        if ($request->hasFile('photos')) {
$file = $request->file('photos');


            $i++;
            $extension = $file->getClientOriginalExtension();
            $destinationPath = 'img/cakes/';
            $fileName = str_replace(" ","_",strtolower($request['name']))."_".$id."_small_".$i.".jpg";

            if($file->move($destinationPath, $fileName))
            {

            }
            else {
                return [
                'status' => 'error',
                'message' => 'image_upload',
                'errors' => 'photo upload failed'
                ];
            }


copy($destinationPath.str_replace(" ","_",strtolower($request['name']))."_".$id."_small_".$i.".jpg", $destinationPath.str_replace(" ","_",strtolower($request['name']))."_".$id."_large_".$i.".jpg");

      }
      else {
        if ($oldName != $request['name']) {
          rename("img/cakes/".str_replace(" ","_",strtolower($oldName))."_".$id."_small_1.jpg", "img/cakes/".str_replace(" ","_",strtolower($request['name']))."_".$id."_small_1.jpg");
          rename("img/cakes/".str_replace(" ","_",strtolower($oldName))."_".$id."_large_1.jpg", "img/cakes/".str_replace(" ","_",strtolower($request['name']))."_".$id."_large_1.jpg");
        }
      }
return redirect('cake-'.$id.'-view/');


    }

    public function addCakeForm(Request $request) {

            return view('admin.addCakeForm');


    }


    public function addCake(Request $request) {

        $this->validate($request, [
                        'is_shaped' => 'required',
                        'is_shaped_avail' => 'required',
                        'is_photo_avail' => 'required',
                        'name' => 'required',
                        'amount' => 'required',
                        'photos' => 'required',
                        'minimum_kg' => 'required'
                        ]);

        if($request['is_shaped'] == "1") {
                          $request['amount'] = 35;
                        }
                        if (Cakes::where('name',$request['name'])->count() > 0) {
                          return Redirect::back()->withErrors(['Cake with this name already exist.']);
                        }
        $cake = Cakes::create([
                    'description' => $request['description'],
                    'is_shaped' => $request['is_shaped'],
                    'shaped_amount' => $request['amount'],
                    'is_shaped_avail' => $request['is_shaped_avail'],
                    'is_photo_avail' => $request['is_photo_avail'],
                    'name' => $request['name'],
                    'amount' => $request['amount'],
                    'minimum_kg' => $request['minimum_kg'],
                    'discount' => 0,
                    'discount_type' => 0,
                    'cake_type_id' => 1,
                    'avail_from' => '10:00:00',
                    'avail_to' => '21:00:00',
                    'pic_count' => 1,
                    'status' => 1,
                                       ]);
        $id = $cake['id'];

        $i = 0;
        $file = $request->file('photos');
            $i++;
            $extension = $file->getClientOriginalExtension();
            $destinationPath = 'img/cakes/';
            $fileName = str_replace(" ","_",strtolower($request['name']))."_".$id."_small_".$i.".jpg";

            if($file->move($destinationPath, $fileName))
{

            }
            else {
                return [
                'status' => 'error',
                'message' => 'image_upload',
                'errors' => 'photo upload failed'
                ];
            }

copy($destinationPath.str_replace(" ","_",strtolower($request['name']))."_".$id."_small_".$i.".jpg", $destinationPath.str_replace(" ","_",strtolower($request['name']))."_".$id."_large_".$i.".jpg");

        return redirect('cake-'.$id.'-view/');

    }

    public function viewOrder(Request $request) {

        $order = Orders::where('id',$request->id)->first();

        $cakes = OrderCakes::where('order_id',$order["id"])->get();
        $user = User::where('id',$order["user_id"])->first();
        $emirate = Emirates::where('id',$order["emirate_id"])->first();
        $timeslot = Timeslots::where('id',$order["timeslot_id"])->first();

        return view('admin.viewOrder')->with('order',$order)->with('cakes',$cakes)->with('user',$user)->with('emirate',$emirate)->with('timeslot',$timeslot);

    }


    public function viewUsers(Request $request) {

        $users = User::orderBy('id', 'desc')->paginate(20);

        return view('admin.viewUsers')->with('users',$users);

    }

    public function viewSliders(Request $request) {

        $sliders = Slider::orderBy('id', 'desc')->where('status',1)->paginate(20);


        return view('admin.viewSliders')->with('sliders',$sliders);

    }

    public function viewSlider(Request $request) {

        $slider = Slider::where('id', $request->id)->first();

        return view('admin.viewSlider')->with('slider',$slider);

    }

    public function addSliderForm(Request $request) {



        return view('admin.addSlider');

    }

    public function addSlider(Request $request) {

      $this->validate($request, [
                      'image' => 'required',
                  ]);


        $image = "";
        if ($request->hasFile('image')) {
          $extension = $request->file('image')->getClientOriginalExtension();
          $destinationPath = 'img/slider/';
          $fileName = str_replace(" ","",uniqid('img_', true).microtime()).'.'.$extension;

          if($request->file('image')->move($destinationPath, $fileName))
          {
          $image = $destinationPath.$fileName;
         }
           else {
             return Redirect::back()->withErrors(['Image upload problem. please try again']);
           }
        } else {
          return Redirect::back()->withErrors(['Select slider image']);
        }

          $slider = Slider::create([
            'image' => $image,
            'title' => $request['title'],
            'description' => $request['description'],
            'order_id' => 1
          ]);

          return redirect('slider-'.$slider['id'].'-view/');



    }


    public function editSlider(Request $request) {

      $this->validate($request, [
                      'slider_id' => 'required|exists:slider,id',
                  ]);

        $slider = Slider::where('id', $request['slider_id'])->first();
        $image = "";
        if ($request->hasFile('image')) {
          $extension = $request->file('image')->getClientOriginalExtension();
          $destinationPath = 'img/slider/';
          $fileName = str_replace(" ","",uniqid('img_', true).microtime()).'.'.$extension;

          if($request->file('image')->move($destinationPath, $fileName))
          {
          $image = $destinationPath.$fileName;
         }
           else {
             return Redirect::back()->withErrors(['Image upload problem. please try again']);
           }
        }
        if ($request->hasFile('image')) {
         Slider::where('id', $request['slider_id'])->update([
           'image' => $image,
            'title' => $request['title'],
            'description' => $request['description']
          ]);
        } else {
          Slider::where('id', $request['slider_id'])->update([
             'title' => $request['title'],
             'description' => $request['description']
           ]);
        }

          return redirect('slider-'.$request['slider_id'].'-view/');


    }



    public function viewCakes(Request $request) {

        $cakes = Cakes::where('status',1)->orderBy('id', 'desc')->paginate(20);

        return view('admin.viewCakes')->with('cakes',$cakes);

    }


}
