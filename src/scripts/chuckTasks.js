import { chuckAjax } from "./chuckAjax"

export const chuckTasks = () => {
    if(document.body.contains(document.querySelector('button[data-task]'))) {

        const triggers   = document.querySelectorAll('button[data-task]')

        const resultContainer   = document.querySelector('[data-results]');

        triggers.forEach((tr) => {
            tr.addEventListener('click', (e) => {
                e.preventDefault()

                let task    = e.target.getAttribute('data-task')
                let fd = new FormData()

                fd.append('ch_task', task)
    
                new chuckAjax().callApi('/cordell.php', fd, (data) => {
                    let result  = JSON.parse(data)

                    resultContainer.innerHTML = ''
                    resultContainer.innerHTML = result.content
                })
            })
        })
    }
}
