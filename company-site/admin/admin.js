// Зургийг хуулахаас өмнө хөтөч дээр нь багасгана: том зураг хостингийн хэмжээний хязгаарт
// орохгүй байхаар урт талыг нь MAX_SIDE пиксел болгож, JPEG чанарыг бага зэрэг бууруулна.
(function () {
  var MAX_SIDE = 1920;
  var QUALITY = 0.85;
  var maxUpload = Number(document.body.getAttribute('data-max-upload')) || 0;
  var maxPost = Number(document.body.getAttribute('data-max-post')) || 0;

  function canvasToBlob(canvas, type, quality) {
    return new Promise(function (resolve) { canvas.toBlob(resolve, type, quality); });
  }

  function shrink(file) {
    if (!/^image\/(jpeg|png|webp)$/.test(file.type) || !window.createImageBitmap) return Promise.resolve(file);
    return createImageBitmap(file, { imageOrientation: 'from-image' }).then(function (bmp) {
      var scale = Math.min(1, MAX_SIDE / Math.max(bmp.width, bmp.height));
      if (scale === 1 && file.size < 1.5e6) return file;
      var canvas = document.createElement('canvas');
      canvas.width = Math.round(bmp.width * scale);
      canvas.height = Math.round(bmp.height * scale);
      canvas.getContext('2d').drawImage(bmp, 0, 0, canvas.width, canvas.height);
      // PNG-ийн тунгалаг дэвсгэрийг (лого г.м.) хадгална
      var png = file.type === 'image/png';
      return canvasToBlob(canvas, png ? 'image/png' : 'image/jpeg', QUALITY).then(function (blob) {
        if (!blob || blob.size >= file.size) return file;
        return new File([blob], file.name.replace(/\.[^.]+$/, '') + (png ? '.png' : '.jpg'), { type: blob.type });
      });
    }).catch(function () { return file; });
  }

  function mb(n) { return (n / 1048576).toFixed(1) + 'MB'; }

  document.querySelectorAll('form[enctype="multipart/form-data"]').forEach(function (form) {
    form.addEventListener('submit', function (ev) {
      if (form.getAttribute('data-ready')) return;
      var inputs = Array.prototype.filter.call(form.querySelectorAll('input[type=file]'), function (i) { return i.files.length; });
      if (!inputs.length || !window.DataTransfer) return;
      ev.preventDefault();
      var button = form.querySelector('button.primary');
      var label = button ? button.textContent : '';
      if (button) { button.disabled = true; button.textContent = 'Зураг бэлдэж байна…'; }

      Promise.all(inputs.map(function (input) {
        return Promise.all(Array.prototype.map.call(input.files, shrink)).then(function (files) {
          var dt = new DataTransfer();
          files.forEach(function (f) { dt.items.add(f); });
          input.files = dt.files;
          return files;
        });
      })).then(function (groups) {
        var all = [].concat.apply([], groups);
        var total = all.reduce(function (s, f) { return s + f.size; }, 0);
        var big = all.filter(function (f) { return maxUpload && f.size > maxUpload; });
        var problem = big.length
          ? big.map(function (f) { return f.name + ' (' + mb(f.size) + ')'; }).join(', ') + ' — хэт том байна. Хостингийн хязгаар: ' + mb(maxUpload)
          : (maxPost && total > maxPost ? 'Нийт ' + mb(total) + ' байна. Нэг удаад ' + mb(maxPost) + '-аас ихгүй байх ёстой — цөөн зургаар хувааж хуулна уу.' : '');
        if (problem) {
          alert(problem);
          if (button) { button.disabled = false; button.textContent = label; }
          return;
        }
        form.setAttribute('data-ready', '1');
        form.submit();
      });
    });
  });
})();
