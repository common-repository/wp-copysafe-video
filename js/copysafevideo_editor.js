(function () {
  tinymce.create('tinymce.plugins.copysafevideo', {
    init: function (ed, url) {
      ed.addButton('copysafevideo', {
        title: 'CopySafe Video',
        image: url + '/images/copysafevideobutton.png',
        onclick: function () {
          var name = prompt("Name of the class file", "");
          if (name != null && name != '')
            ed.execCommand('mceInsertContent', false, '[copysafevideo name="'+name+'"][/copysafevideo]');
        }
      });
    },
    createControl: function (n, cm) {
      return null;
    },
    getInfo: function () {
      return {
        longname: "CopySafe Video",
        author: 'ArtistScope',
        authorurl: 'https://artistscope.com/',
        infourl: 'https://artistscope.com/copysafe_video_protection_wordpress_plugin.asp',
        version: "0.1"
      };
    }
  });
  tinymce.PluginManager.add('copysafevideo', tinymce.plugins.copysafevideo);
})();