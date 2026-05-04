{{$response = Package.Raxon.Task:Service:read(flags(), options())}}
{{$response|>object:'json'}}