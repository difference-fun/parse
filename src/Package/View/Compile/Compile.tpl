{{R3M}}
{{$response = Package.Difference.Fun.Parse:Main:compile(flags(), options())}}
{{if($response)}}
{{$response|object:'json'}}

{{/if}}