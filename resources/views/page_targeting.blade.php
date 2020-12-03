@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('save_data') }}" id="create-snippet">
                        @csrf
                        <div class="form-group row">
                            <label for="message" class="col-md-2 col-form-label">Popup Message</label>

                            <div class="col-md-3">
                                <input id="message" type="text" class="form-control" name="message" value="{{$message ?? ''}}" required placeholder="Welcome to my page">
                            </div>
                        </div>
                        <div id="rules">
                            @if(isset($rules_count) && $rules_count)
                                @foreach($rules as $key => $value)
                                <div class="form-group row rules-row">
                                    <div class="col-md-2">
                                        <select class="form-control constraint-input" name="rules[{{$key}}][constraint]">
                                            <option value="show" @if($value['constraint'] == 'show') selected @endif>Show On</option>
                                            <option value="dont_show" @if($value['constraint'] == 'dont_show') selected @endif>Don't show On</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-control rule-input" name="rules[{{$key}}][rule]">
                                            <option value="contains" @if($value['rule'] == 'contains') selected @endif>Pages that contains</option>
                                            <option value="start_with" @if($value['rule'] == 'start_with') selected @endif>Pages starting with</option>
                                            <option value="end_with" @if($value['rule'] == 'end_with') selected @endif>Pages ending with</option>
                                            <option value="exact" @if($value['rule'] == 'exact') selected @endif>A specific page</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="value" class="col-form-label">www.domain.com/</label>
                                    </div>    
                                    <div class="col-md-4">
                                        <input id="value" type="text" class="form-control value-input" name="rules[{{$key}}][value]" value="{{$value['value'] ?? ''}}" required placeholder="Enter text">
                                    </div>
                                    @if($key != 0)
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-danger" id="remove-rules">X</button>
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            @else
                            <div class="form-group row rules-row">
                                <div class="col-md-2">
                                    <select class="form-control constraint-input" name="rules[0][constraint]">
                                        <option value="show">Show On</option>
                                        <option value="dont_show">Don't show On</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control rule-input" name="rules[0][rule]">
                                        <option value="contains">Pages that contains</option>
                                        <option value="start_with">Pages starting with</option>
                                        <option value="end_with">Pages ending with</option>
                                        <option value="exact">A specific page</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="value" class="col-form-label">www.domain.com/</label>
                                </div>    
                                <div class="col-md-4">
                                    <input id="value" type="text" class="form-control value-input" name="rules[0][value]" value="" required placeholder="Enter text">
                                </div>
                            </div>
                            @endif
                        </div>
                        <div class="form-group row mb-0">
                            <div class="col-md-6">
                                <button class="btn btn-secondary" id="add-rule">Add rule</button>
                                <button type="submit" class="btn btn-primary">Save Rules</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div id="show-response" class="alert alert-secondary" style="display: none;" role="alert"></div>
            </div>
        </div>
    </div>
</div>

@php 
    $count = isset($rules_count) ? $rules_count : 1;
@endphp
<script type="text/javascript">
    $(document).ready(function(){

        var input_row_count = "{{$count}}";

        // Add new rule row..
        $('#add-rule').click(function(e){
            e.preventDefault();
            
            var inputs_row = $('.rules-row:first').clone();//.appendTo('#rules').show();
            inputs_row.find('.constraint-input').attr('name', "rules["+input_row_count+"][constraint]");
            inputs_row.find('.rule-input').attr('name', "rules["+input_row_count+"][rule]");
            inputs_row.find('.value-input').attr('name', "rules["+input_row_count+"][value]");
            inputs_row.find('.value-input').attr('value', "");
            inputs_row.append("<div class='col-md-1'><button type='button' class='btn btn-danger' id='remove-rules'>X</button></div>");
            inputs_row.appendTo('#rules').show();
            input_row_count++;
        });

        // Remove Rule..
        $(document).on('click', '#remove-rules', function(e){
            e.preventDefault();
            $(this).parent().parent().remove();
        });

        // Save Rules and Creare Snippet
        $(document).on("submit","#create-snippet", function(e){
            // alert('asdasd');
            e.preventDefault();
            var form = $(this).serializeArray();
            
            $.ajax(
            {
                type:'POST',
                url: '/save_data',
                dataType: "JSON",
                data: form,

                success: function (response)
                {
                    if(response.status) {
                        // Show Snippet
                        $('#show-response').text(response.snippet);
                    }else {
                        // SHow Error Message..
                        $('#show-response').text(response.error_message);
                    }
                    
                    $('#show-response').show();
                }
            });

            return false;
        });
    })
</script>
@endsection
