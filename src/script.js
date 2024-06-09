const indexUrl = 'http://localhost/index.php?';
const tableId = 'data-table';
let loadedData;
document.getElementById("search-input").addEventListener('input', function () {
    if (this.value == '') {
        displayData(loadedData);
    } else {
        applySearch(this.value);
    }
});
document.getElementById("image-input").addEventListener('change', function () {
    console.log(this.files[0])
    document.getElementById("image-preview").src =this.files[0];
    var reader = new FileReader();
    reader.onload = function (e) {
        $('#image-preview')
            .attr('src', e.target.result)
            .width(350)
            .height(250);
    };
    reader.readAsDataURL(this.files[0]);
});
loadData();
setInterval(loadData, 60000);
function loadData() {
    fetch(indexUrl + new URLSearchParams({
        name: 'getData',
    }))
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            clearTable();
            loadedData = data;
            displayData(data);
        })
        .catch(error => {
            console.log('Error:', error);
        });
}
function displayData(data) {
    var tbodyRef = document.getElementById(tableId).getElementsByTagName('tbody')[0];
    data.forEach(function (item) {
        var newRow = tbodyRef.insertRow();
        newRow.classList.add('row');
        if (item.colorCode) {
            newRow.style.color = item.colorCode;
        }

        createCell(newRow, "col-md-2", document.createTextNode(item.task));
        createCell(newRow, "col-md-3", document.createTextNode(item.title));
        createCell(newRow, "col-md-6", createDescription(item));
        createCell(newRow, "col-md-2", createColor(item));
    });
}

function createColor(item) {
    var colorSpan = document.createElement("span");
    if (item.colorCode) {
        colorSpan.innerText = item.colorCode;
        colorSpan.style.backgroundColor = item.colorCode;
        colorSpan.style.color = '#000';
    } else {
        colorSpan.innerText = 'no color';
    }

    return colorSpan;
}

function createDescription(item) {
    var description = document.createElement("p");
    description.innerText = item.description;
    description.style.maxWidth = '40%';

    return description;
}

function createCell(row, styles, value) {
    var taskCell = row.insertCell();
    taskCell.classList.add(styles)
    taskCell.appendChild(value);
}

function clearTable() {
    $('#'+tableId+' tbody').empty();
}

function applySearch(searchedText) {
    let newData = [];
    loadedData.forEach(function (item) {
        if (item.task.search(searchedText) >= 0 ||
            item.title.search(searchedText) >= 0 ||
            item.description.search(searchedText) >= 0 ||
            item.colorCode.search(searchedText) >= 0
        ) {
            newData.push(item)
        }
    });
    clearTable();
    displayData(newData);
}