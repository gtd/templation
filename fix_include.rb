#!/usr/bin/env ruby -w

match = /(include(?:_once)?\(\s*(?:'|"))(Templation\/driver\.php(?:'|")\s*\);?)/
replace = '\1\2'
ignore_filenames = /\.svn/
ignore = /IGNORE_FILES_CONTAINING_THIS_REGULAR_EXPRESSION/

$stdin.each do |arg|
  arg.strip!
  if !(arg =~ ignore_filenames)
    if FileTest::exist? arg
      open arg do |f|
        f.gets nil
        if ~match
          if ~ignore 
            print "IGNORED  #{arg}\n" 
          else 
            gsub! match, replace
            open arg, 'w' do |fileout|
              fileout.write $_
            end
            print "REPLACED #{arg}\n"
          end
        end
      end
    else
      print "Error: #{arg} is not a file.\n"
    end
  else
    print "SKIPPED #{arg}\n"
  end
end
