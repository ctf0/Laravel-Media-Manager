const musicMetadata = require('music-metadata-browser')

onmessage = async (e) => {
    let url = e.data

    let val = await musicMetadata.fetchFromUrl(url)
    let res = val.common
    let picture = res.picture

    postMessage({
        artist      : res.artist,
        title       : res.title,
        album       : res.album,
        track       : res.track.no,
        track_total : res.track.of,
        year        : res.year,
        genre       : res.genre ? res.genre[0] : null,
        cover       : picture
            ? URL.createObjectURL(new Blob([picture[0].data.buffer]))
            : null
    })
}
