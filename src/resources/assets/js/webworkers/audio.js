const musicMetadata = require('music-metadata-browser')

self.addEventListener('message', (e) => {
    let url = e.data

    musicMetadata.fetchFromUrl(url)
        .then((val) => {
            let res = val.common
            let picture = res.picture

            self.postMessage({
                artist: res.artist,
                title: res.title,
                album: res.album,
                track: res.track.no,
                track_total: res.track.of,
                year: res.year,
                genre: res.genre ? res.genre[0] : null,
                cover: picture
                    ? URL.createObjectURL(new Blob([picture[0].data.buffer]))
                    : null
            })
        })
})
