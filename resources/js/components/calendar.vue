<template>
    <div>
        <div class="component-row">
            <div class="component-col-left p-2">
                <form @submit.prevent="addEvents">
                    <div class="form-floating mb-3">
                        <label for="event">Event</label>
                        <input type="text" class="form-control" id="event" v-model="eventInfo.event">
                    </div>
                    <div class="form-floating">
                        <label for="from">From</label>
                        <input type="date" class="form-control" id="from" v-model="eventInfo.from" min="2021-01-01" max="2021-01-30">
                    </div>
                    <div class="form-floating mb-4">
                        <label for="to">To</label>
                        <input type="date" class="form-control" id="to" v-model="eventInfo.to" min="2021-01-01" max="2021-01-30">
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="dayOpt" name="mon" value="Monday" v-model="recurrence">
                        <label class="form-check-label" for="dayOpt">Mon</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="dayOpt" value="Tuesday" v-model="recurrence">
                        <label class="form-check-label" for="dayOpt">Tue</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="dayOpt" value="Wednesday" v-model="recurrence">
                        <label class="form-check-label" for="dayOpt">Wed</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="dayOpt" value="Thursday" v-model="recurrence">
                        <label class="form-check-label" for="dayOpt">Thu</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="dayOpt" value="Friday" v-model="recurrence">
                        <label class="form-check-label" for="dayOpt">Fri</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="dayOpt" value="Saturday" v-model="recurrence">
                        <label class="form-check-label" for="dayOpt">Sat</label>
                    </div>
                    <div class="form-check form-check-inline mb-3">
                        <input class="form-check-input" type="checkbox" id="dayOpt" value="Sunday" v-model="recurrence">
                        <label class="form-check-label" for="dayOpt">Sun</label>
                    </div>
                    <div class="form-floating">
                        <button type="submit" class="btn btn-primary btn-sm">Submit</button>
                    </div>
                </form>
            </div>
            <div class="component-col-right p-2">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <h3>{{moment(this.month).format('MMM YYYY')}}</h3>
                    </li>

                    <li class="list-group-item" v-for="cal in calendar" :class = "cal.event ? 'bg-light' : ''">
                        <span class="mr-5">{{ moment(cal.date).format('D ddd') }}</span>
                        <span class="mr-5">{{ cal.event }}</span>
                    </li>

                </ul>
            </div>
        </div>
    </div>
</template>

<script>

import moment from 'moment';
import Toasted from 'vue-toasted';

Vue.prototype.moment = moment;
Vue.use(Toasted);

export default {

    data() {
        return {
            calendar: [],
            eventInfo: {
                event: 'Event',
                from: '',
                to: '',
                days: [],
            },
            recurrence: [],
            month: moment().format("YYYY-MM"),
        }
    },

    created() {
        this.fetchEvents();
    },
    
    computed: {
        getMonthDates() {
            var daysInMonth =  moment(this.month).daysInMonth();
            var days = [];
            for (var i = 1; i < daysInMonth + 1; i++) {
                var date = moment().month(this.month).date(i).format('YYYY-MM-DD');
                days.push(date);
            }
            return days;
        },
    },

    methods: {

        fetchEvents() {
            fetch('/api/events').then(res => res.json())
            .then(res => {
                var cal = [];
                
                $.each(this.getMonthDates, function(key, value) {
                    cal.push({'date' : value});
                });

                const merged = [...res.data, ...cal];
                let set = new Set();
                let events = merged.filter(item => {
                    if (!set.has(item.date)) {
                        set.add(item.date);
                        return true;
                    }
                    return false;
                }, set);

                events.sort(function(a, b) {
                    var keyA = new Date(a.date),
                        keyB = new Date(b.date);
                        if (keyA < keyB) return -1;
                        if (keyA > keyB) return 1;
                        return 0;
                });

                this.calendar = events;
            })
            .catch(err => console.log(err));
        },

        addEvents () {
            
            this.eventInfo.days = this.recurrence;

            fetch('api/submit', {
                    method: 'post',
                    body: JSON.stringify(this.eventInfo),
                    headers: {
                        'content-type': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    this.clearForm();
                    let toast = this.$toasted.show("Event successfully added!", { 
                        theme: "toasted-primary", 
                        position: "top-right", 
                        duration : 2000
                    });
                    this.fetchEvents();
                })
                .catch(err => console.log(err));
        },

        clearForm() {
            this.eventInfo.event = '';
            this.eventInfo.from = '';
            this.eventInfo.to = '';
            this.recurrence = [];
        }
    },
}

</script>