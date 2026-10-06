{{$register = Package.Raxon.Task:Init:register()}}
{{if(!is.empty($register))}}
{{Package.Raxon.Task:Import:role.system()}}
{{Package.Raxon.Task:Setup:task.install(flags(), options())}}
{{/if}}