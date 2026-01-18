⌛ Time Tracker App – Product Specification
1. Vision

Time Tracker is a lightweight, project-based time tracking app designed for creators and solo workers who want clarity without complexity.

Most time tracking tools feel corporate and heavy. This app is different.
It focuses on:

Speed

Simplicity

Visual clarity

Motivation through insight, not guilt

The goal is:

Start working in one click. Understand your time in one glance.

No tasks.
No deadlines.
No project management ceremony.
Just projects, sessions, and honest visibility into where time goes.

It should feel:

Calm while working

Powerful when reviewing

Satisfying to open and use

2. Core Principle

The app has one primary action:

Select a project and press Start to begin tracking time.

Everything else exists to support, visualize, and make sense of that action.

If this interaction is not perfect, the app fails.

3. Core User Flow

Open app

Select an existing project or create one

Press Start

Work

Press Stop

Optionally add a short note

Done

That is the entire loop.

4. App Structure

The app has two modes with different moods.

4.1 Focus Mode (Tracking)

Purpose: Help the user start working instantly.

Characteristics:

Minimal UI

Calm

Low visual noise

Large, central Start / Stop button

Clear active project indicator

Large timer display

No charts

No clutter

This is the “work mode”.

4.2 Insight Mode (Dashboard)

Purpose: Help the user understand how their time is being used.

This is where the AI-style dashboard aesthetic lives.

Includes:

Time worked today

Time worked this week

Time per project (bar chart)

Daily activity timeline

Session history list

Trends over time

This is the “control room”.

5. Tech Stack (Chosen)

This project will be built as a single Laravel web app with an SPA-like experience using Inertia.

Backend

Laravel

MySQL

Laravel migrations

Eloquent ORM

Policies and middleware

Frontend

Inertia.js

Vue 3

Vite

TailwindCSS

Chart.js

Authentication

Laravel Breeze (Inertia + Vue preset)

Login

Registration

Password reset

Authentication exists so users can sync across devices.

6. Architecture Overview

Laravel handles routing, auth, validation, and database persistence

Inertia renders Vue pages and passes props

Vue handles:

Timer state

UI interactions

Charts

Filters

The app uses Laravel’s cookie-based session authentication.

7. Data Ownership

All data is scoped per user:

A user owns many projects

A user owns many sessions

8. Data Model
Project

Fields:

id

user_id

name

color

archived_at (nullable)

created_at

updated_at

Derived:

total_time

last_worked_at

Session

Fields:

id

user_id

project_id

started_at

ended_at (nullable)

duration_seconds (nullable until ended)

note (optional)

created_at

updated_at

9. Features (MVP)

The MVP will not focus on creating accounts or anything. It will focus on storing data in a local mysql database. The goal here is to get the bones of the app up and running, and we'll develop the rest of this stuff later on.
That being said, we want to set things up now so it's a bit easier to expand on it later, but keep it minimal still.

No log in right now.

Projects and sessions are stored in a local mysql database.

Project Management

Create project

Edit project

Archive / unarchive

View total time and last worked

Time Tracking

Start session

Stop session

Only one active session at a time

Auto-calculate duration

Optional note

Resume suggestion

Rules:

Starting a new session stops the previous one

Active session persists across refreshes

10. Focus Mode UI

Large Start / Stop button with UI centered in the screen.

Active project display

Timer (HH:MM:SS)

Minimal layout

Optional glow when running

11. Insight Mode UI

Dark technical dashboard style.

Panels:

Today summary

Weekly summary

Time per project (bar chart)

Daily trend (line chart)

Session history

Filters:

Today

7 days

30 days

Charts:

Chart.js

Clean gridlines

Empty/loading states

12. Backend Routes (MVP)

GET /app

POST /sessions/start

POST /sessions/stop

POST /projects

PUT /projects/{id}

POST /projects/{id}/archive

POST /projects/{id}/unarchive

Optional:

GET /dashboard/summary?range=7d

13. Design Language
Theme

Dark technical dashboard aesthetic
Inspired by:

AI dashboards

Ops consoles

Mission control UIs

Two moods:

Focus Mode → calm and minimal

Insight Mode → dense (but still minimal) and powerful

Colors

Base:

--bg-main

--bg-panel

--border-muted

Accents:

--accent-primary

--accent-success

--accent-warning

--accent-danger

--accent-purple

Typography

Standard font for labels

Monospace for:

Timers

Durations

Stats

Logs

Use tabular-nums.

Layout

Grid-based

Card/panel system

Rounded corners

Borders over shadows

Dense but readable

Motion

Subtle glow for active timer

Smooth transitions

Calm and professional

14. Personality

The app should feel like:

“I’m not here to manage you. I’m just here to show you what happened.”

No guilt.
No pressure.
No productivity theater.

15. Future Ideas (Not MVP)

Tags per session

Workflow templates (YouTube presets)

Weekly goals

Heatmap calendar

CSV export

Shareable reports

Subscriptions

Email summaries

PWA support

16. Summary

This is not a project manager.
It is a work mirror.

A simple tool that shows you where your time actually goes, wrapped in a sleek, sci-fi-inspired interface that makes productivity feel cool and fun.

Primary action:

Choose project → Start timer.