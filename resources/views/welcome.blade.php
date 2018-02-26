@extends('layouts.app')

@section('style')
<link rel='stylesheet' type='text/css' href='/css/welcome/welcome.css' />
@parent
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">Welcome</div>

                <div class="panel-body">
                    <table>
                        <thead>
                            <tr>
                                <td>Version</td>
                                <td>Info</td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1.144</td>
                                <td>
                                    <ul>
                                        <li>Fixed disabling of accounts from accounts list page</li>
                                        <li>Accounts list view now asynchronous</li>
                                        <li>Security improvements on account list page (only required information is returned from the server)</li>
                                        <li>Changed "parent name" in list view to link to parent account</li>
                                        <li>Changed "account name" in list view to link to account edit</li>
                                    </ul>
                                </td>
                            </tr>
                            <tr>
                                <td>1.143</td>
                                <td>
                                    <ul>
                                        <li>Fix bill pickup/delivery commission</li>
                                        <li>Correct invoice deletion route</li>
                                        <li>Removed obsolete invoice options table and adjusted seeders</li>
                                        <li>New accounts now autofill billing address name based on Company name (will require ctrl + F5 first time)</li>
                                    </ul>
                                </td>
                            </tr>
                            <tr>
                                <td>1.142</td>
                                <td>
                                    <ul>
                                        <li>Manifest creation view added</li>
                                        <li>Manifest preview basic view added</li>
                                        <li>Added logic for creation of and management of Manifests</li>
                                        <li>Changed pickup and delivery drivers from optional to mandatory fields on bill object</li>
                                        <li>Adjusted manifests table to accurately reflect date created</li>
                                    </ul>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
