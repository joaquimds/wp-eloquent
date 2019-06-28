require 'json'

wp_version_report = 'reports/wp_version.json'

if File.file?(wp_version_report) and not File.zero?(wp_version_report)
    version = JSON.parse(File.read(wp_version_report)).first['version']
    fail("WordPress out of date. Please update to version #{version}")
end

wp_plugin_report = 'reports/wp_plugin_versions.json'

if File.file?(wp_plugin_report) and not File.zero?(wp_plugin_report)
    JSON.parse(File.read(wp_plugin_report)).each do |line|
        next if line['update'] != 'available'
        fail("Plugin #{line['name']} is out of date. The current version is #{line['version']}. Please update to version #{line['update_version']}.")
    end
end