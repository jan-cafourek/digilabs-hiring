class chuckAjax {
    callApi(url, formdata, callback) {
        this.showLoadingBar()
        let hideBar = () => {
            this.hideLoadingBar()
        }

        fetch(this.getUrl(url), {
            method: 'POST',
            body: formdata
        }).then(function (resp) {
            if (resp.ok)
                return resp.text()
            else {
                hideBar()
                alert("Nastala chyba při komunikaci se serverem")
                return Promise.reject(resp)
            }
        }).then(function (data) {
            callback(data)
            hideBar()
        }).catch(function (err) {
            // There was an error
            hideBar()
            console.warn('Something went wrong.', err)
        })
    }

    showLoadingBar() {
        let barEl = document.getElementById("ch_overlay")
        if (barEl !== null) {
            barEl.style.display = "block"
        }
    }

    hideLoadingBar() {
        let barEl = document.getElementById("ch_overlay")
        if (barEl !== null) {
            barEl.style.display = "none"
        }
    }

    getUrl(urlPart) {
        return "http://" + window.location.host + urlPart
    }
}

export { chuckAjax }