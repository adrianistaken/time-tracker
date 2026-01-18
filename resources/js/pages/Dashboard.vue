<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { Clock, Play, TrendingUp, Calendar, Folder, Pencil, Archive, ArchiveRestore, MoreHorizontal } from 'lucide-vue-next';
import { computed, ref } from 'vue';

import BarChart from '@/components/charts/BarChart.vue';
import LineChart from '@/components/charts/LineChart.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
    DialogClose,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { formatDuration, formatRelativeTime } from '@/lib/utils';
import type {
    ActiveSession,
    DailyTrendItem,
    Project,
    ProjectBreakdownItem,
    Session,
} from '@/types';

import { start as startSession } from '@/actions/App/Http/Controllers/SessionController';
import {
    store as storeProject,
    update as updateProject,
    archive as archiveProject,
} from '@/actions/App/Http/Controllers/ProjectController';
import { show as showSession } from '@/actions/App/Http/Controllers/SessionController';

interface Props {
    projects: Project[];
    colors: string[];
    todaySeconds: number;
    weekSeconds: number;
    projectBreakdown: ProjectBreakdownItem[];
    dailyTrend: DailyTrendItem[];
    recentSessions: Session[];
    activeSession: ActiveSession | null;
    range: string;
}

const props = defineProps<Props>();

// Start session form
const selectedProjectId = ref<string>(props.projects[0]?.id?.toString() ?? '');
const startForm = useForm({
    project_id: '',
});

const handleStartSession = () => {
    startForm.project_id = selectedProjectId.value;
    startForm.submit(startSession());
};

// New project form
const showNewProjectModal = ref(false);
const newProjectForm = useForm({
    name: '',
    color: props.colors[0],
});

const handleCreateProject = () => {
    newProjectForm.post(storeProject().url, {
        onSuccess: () => {
            showNewProjectModal.value = false;
            newProjectForm.reset();
        },
    });
};

// Edit project
const showEditProjectModal = ref(false);
const editingProject = ref<Project | null>(null);
const editProjectForm = useForm({
    name: '',
    color: '',
});

const openEditModal = (project: Project) => {
    editingProject.value = project;
    editProjectForm.name = project.name;
    editProjectForm.color = project.color;
    showEditProjectModal.value = true;
};

const handleUpdateProject = () => {
    if (!editingProject.value) return;

    editProjectForm.put(updateProject(editingProject.value.id).url, {
        onSuccess: () => {
            showEditProjectModal.value = false;
            editingProject.value = null;
            editProjectForm.reset();
        },
    });
};

// Archive project
const handleArchiveProject = (project: Project) => {
    router.post(archiveProject(project.id).url);
};

// Chart data
const barChartData = computed(() => ({
    labels: props.projectBreakdown.map((p) => p.name),
    data: props.projectBreakdown.map((p) => p.total_seconds / 3600),
    colors: props.projectBreakdown.map((p) => p.color),
}));

const lineChartData = computed(() => ({
    labels: props.dailyTrend.map((d) => d.date),
    data: props.dailyTrend.map((d) => d.hours),
}));

// Range filter
const handleRangeChange = (value: string) => {
    router.get('/dashboard', { range: value }, { preserveState: true });
};
</script>

<template>
    <Head title="Dashboard" />

    <div class="min-h-screen bg-zinc-950 text-zinc-100">
        <!-- Header -->
        <header class="border-b border-zinc-800/50 bg-zinc-950/80 backdrop-blur-sm">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-6">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-500/20">
                        <Clock class="h-4 w-4 text-indigo-400" />
                    </div>
                    <span class="text-lg font-semibold tracking-tight">Time Tracker</span>
                </div>

                <!-- Active session indicator or Start button -->
                <div class="flex items-center gap-4">
                    <template v-if="activeSession">
                        <Link
                            :href="showSession(activeSession.id).url"
                            class="flex items-center gap-2 rounded-lg bg-emerald-500/10 px-4 py-2 text-emerald-400 transition-colors hover:bg-emerald-500/20"
                        >
                            <span class="relative flex h-2 w-2">
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75" />
                                <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500" />
                            </span>
                            <span class="text-sm font-medium">{{ activeSession.project.name }}</span>
                            <span class="text-xs text-emerald-500">Active</span>
                        </Link>
                    </template>
                    <template v-else>
                        <div class="flex items-center gap-2">
                            <Select v-model="selectedProjectId">
                                <SelectTrigger class="w-48 border-zinc-700 bg-zinc-900 text-zinc-100">
                                    <SelectValue placeholder="Select project" />
                                </SelectTrigger>
                                <SelectContent class="border-zinc-700 bg-zinc-900">
                                    <SelectItem
                                        v-for="project in projects"
                                        :key="project.id"
                                        :value="project.id.toString()"
                                        class="text-zinc-100 focus:bg-zinc-800 focus:text-zinc-100"
                                    >
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="h-2 w-2 rounded-full"
                                                :style="{ backgroundColor: project.color }"
                                            />
                                            {{ project.name }}
                                        </div>
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <Button
                                :disabled="!selectedProjectId || startForm.processing"
                                @click="handleStartSession"
                                class="gap-2 bg-indigo-600 hover:bg-indigo-500"
                            >
                                <Play class="h-4 w-4" />
                                Start
                            </Button>
                        </div>
                    </template>
                </div>
            </div>
        </header>

        <!-- Main content -->
        <main class="mx-auto max-w-7xl px-6 py-8">
            <!-- Summary cards -->
            <div class="mb-8 grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <!-- Today -->
                <Card class="border-zinc-800 bg-zinc-900/50">
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium text-zinc-400">Today</CardTitle>
                        <Calendar class="h-4 w-4 text-zinc-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="font-mono text-2xl font-bold tabular-nums text-zinc-100">
                            {{ formatDuration(todaySeconds) }}
                        </div>
                        <p class="text-xs text-zinc-500">Time tracked today</p>
                    </CardContent>
                </Card>

                <!-- This Week -->
                <Card class="border-zinc-800 bg-zinc-900/50">
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium text-zinc-400">This Week</CardTitle>
                        <TrendingUp class="h-4 w-4 text-zinc-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="font-mono text-2xl font-bold tabular-nums text-zinc-100">
                            {{ formatDuration(weekSeconds) }}
                        </div>
                        <p class="text-xs text-zinc-500">Since Monday</p>
                    </CardContent>
                </Card>

                <!-- Projects -->
                <Card class="border-zinc-800 bg-zinc-900/50">
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium text-zinc-400">Projects</CardTitle>
                        <Folder class="h-4 w-4 text-zinc-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="font-mono text-2xl font-bold tabular-nums text-zinc-100">
                            {{ projects.length }}
                        </div>
                        <p class="text-xs text-zinc-500">Active projects</p>
                    </CardContent>
                </Card>

                <!-- New Project Button -->
                <Card class="flex items-center justify-center border-zinc-800 border-dashed bg-zinc-900/30 transition-colors hover:border-zinc-700 hover:bg-zinc-900/50">
                    <Dialog v-model:open="showNewProjectModal">
                        <DialogTrigger as-child>
                            <button class="flex h-full w-full flex-col items-center justify-center gap-2 p-6 text-zinc-500 transition-colors hover:text-zinc-300">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-dashed border-zinc-700">
                                    <span class="text-xl">+</span>
                                </div>
                                <span class="text-sm">New Project</span>
                            </button>
                        </DialogTrigger>
                        <DialogContent class="border-zinc-800 bg-zinc-900 sm:max-w-md">
                            <DialogHeader>
                                <DialogTitle class="text-zinc-100">Create Project</DialogTitle>
                                <DialogDescription class="text-zinc-400">
                                    Add a new project to track time against.
                                </DialogDescription>
                            </DialogHeader>
                            <form @submit.prevent="handleCreateProject" class="space-y-4">
                                <div class="space-y-2">
                                    <Label for="project-name" class="text-zinc-300">Name</Label>
                                    <Input
                                        id="project-name"
                                        v-model="newProjectForm.name"
                                        placeholder="My Project"
                                        class="border-zinc-700 bg-zinc-800 text-zinc-100 placeholder:text-zinc-500"
                                        autofocus
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-zinc-300">Color</Label>
                                    <div class="flex flex-wrap gap-2">
                                        <button
                                            v-for="color in colors"
                                            :key="color"
                                            type="button"
                                            class="h-8 w-8 rounded-lg transition-transform hover:scale-110"
                                            :class="{
                                                'ring-2 ring-white ring-offset-2 ring-offset-zinc-900': newProjectForm.color === color,
                                            }"
                                            :style="{ backgroundColor: color }"
                                            @click="newProjectForm.color = color"
                                        />
                                    </div>
                                </div>
                                <DialogFooter>
                                    <Button
                                        type="submit"
                                        :disabled="!newProjectForm.name || newProjectForm.processing"
                                        class="bg-indigo-600 hover:bg-indigo-500"
                                    >
                                        Create Project
                                    </Button>
                                </DialogFooter>
                            </form>
                        </DialogContent>
                    </Dialog>
                </Card>
            </div>

            <!-- Charts row -->
            <div class="mb-8 grid gap-6 lg:grid-cols-2">
                <!-- Project breakdown -->
                <Card class="border-zinc-800 bg-zinc-900/50">
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle class="text-zinc-100">Time by Project</CardTitle>
                                <CardDescription class="text-zinc-500">
                                    Hours tracked per project
                                </CardDescription>
                            </div>
                            <Select :model-value="range" @update:model-value="handleRangeChange">
                                <SelectTrigger class="w-28 border-zinc-700 bg-zinc-800 text-zinc-100">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent class="border-zinc-700 bg-zinc-900">
                                    <SelectItem value="today" class="text-zinc-100 focus:bg-zinc-800 focus:text-zinc-100">Today</SelectItem>
                                    <SelectItem value="7d" class="text-zinc-100 focus:bg-zinc-800 focus:text-zinc-100">7 Days</SelectItem>
                                    <SelectItem value="30d" class="text-zinc-100 focus:bg-zinc-800 focus:text-zinc-100">30 Days</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="h-64">
                            <template v-if="projectBreakdown.length > 0">
                                <BarChart
                                    :labels="barChartData.labels"
                                    :data="barChartData.data"
                                    :colors="barChartData.colors"
                                />
                            </template>
                            <template v-else>
                                <div class="flex h-full items-center justify-center text-zinc-500">
                                    No data for this period
                                </div>
                            </template>
                        </div>
                    </CardContent>
                </Card>

                <!-- Daily trend -->
                <Card class="border-zinc-800 bg-zinc-900/50">
                    <CardHeader>
                        <CardTitle class="text-zinc-100">Daily Trend</CardTitle>
                        <CardDescription class="text-zinc-500">
                            Hours tracked over the past 7 days
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="h-64">
                            <LineChart
                                :labels="lineChartData.labels"
                                :data="lineChartData.data"
                            />
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Two column layout: Session history + Projects -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Session history -->
                <Card class="border-zinc-800 bg-zinc-900/50 lg:col-span-2">
                    <CardHeader>
                        <CardTitle class="text-zinc-100">Recent Sessions</CardTitle>
                        <CardDescription class="text-zinc-500">
                            Your latest tracked work sessions
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <template v-if="recentSessions.length > 0">
                            <div class="space-y-3">
                                <div
                                    v-for="session in recentSessions"
                                    :key="session.id"
                                    class="flex items-center justify-between rounded-lg border border-zinc-800 bg-zinc-900/50 p-4"
                                >
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="h-3 w-3 rounded-full"
                                            :style="{ backgroundColor: session.project.color }"
                                        />
                                        <div>
                                            <div class="font-medium text-zinc-200">
                                                {{ session.project.name }}
                                            </div>
                                            <div class="text-sm text-zinc-500">
                                                {{ formatRelativeTime(session.ended_at!) }}
                                                <template v-if="session.note">
                                                    · {{ session.note }}
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="font-mono text-sm tabular-nums text-zinc-400">
                                        {{ formatDuration(session.duration_seconds!) }}
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <div class="py-12 text-center text-zinc-500">
                                <Clock class="mx-auto mb-4 h-12 w-12 opacity-50" />
                                <p>No sessions yet</p>
                                <p class="text-sm">Start tracking to see your history here</p>
                            </div>
                        </template>
                    </CardContent>
                </Card>

                <!-- Projects list -->
                <Card class="border-zinc-800 bg-zinc-900/50">
                    <CardHeader>
                        <CardTitle class="text-zinc-100">Projects</CardTitle>
                        <CardDescription class="text-zinc-500">
                            Manage your projects
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-2">
                            <div
                                v-for="project in projects"
                                :key="project.id"
                                class="flex items-center justify-between rounded-lg border border-zinc-800 bg-zinc-900/50 p-3"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="h-3 w-3 rounded-full"
                                        :style="{ backgroundColor: project.color }"
                                    />
                                    <div>
                                        <div class="text-sm font-medium text-zinc-200">
                                            {{ project.name }}
                                        </div>
                                        <div class="font-mono text-xs tabular-nums text-zinc-500">
                                            {{ formatDuration(project.total_seconds ?? 0) }}
                                        </div>
                                    </div>
                                </div>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" size="sm" class="h-8 w-8 p-0 text-zinc-400 hover:text-zinc-100">
                                            <MoreHorizontal class="h-4 w-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end" class="border-zinc-800 bg-zinc-900">
                                        <DropdownMenuItem
                                            @click="openEditModal(project)"
                                            class="text-zinc-100 focus:bg-zinc-800 focus:text-zinc-100"
                                        >
                                            <Pencil class="mr-2 h-4 w-4" />
                                            Edit
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            @click="handleArchiveProject(project)"
                                            class="text-zinc-100 focus:bg-zinc-800 focus:text-zinc-100"
                                        >
                                            <Archive class="mr-2 h-4 w-4" />
                                            Archive
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </main>

        <!-- Edit Project Modal -->
        <Dialog v-model:open="showEditProjectModal">
            <DialogContent class="border-zinc-800 bg-zinc-900 sm:max-w-md">
                <DialogHeader>
                    <DialogTitle class="text-zinc-100">Edit Project</DialogTitle>
                    <DialogDescription class="text-zinc-400">
                        Update your project details.
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="handleUpdateProject" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="edit-project-name" class="text-zinc-300">Name</Label>
                        <Input
                            id="edit-project-name"
                            v-model="editProjectForm.name"
                            placeholder="My Project"
                            class="border-zinc-700 bg-zinc-800 text-zinc-100 placeholder:text-zinc-500"
                        />
                    </div>
                    <div class="space-y-2">
                        <Label class="text-zinc-300">Color</Label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="color in colors"
                                :key="color"
                                type="button"
                                class="h-8 w-8 rounded-lg transition-transform hover:scale-110"
                                :class="{
                                    'ring-2 ring-white ring-offset-2 ring-offset-zinc-900': editProjectForm.color === color,
                                }"
                                :style="{ backgroundColor: color }"
                                @click="editProjectForm.color = color"
                            />
                        </div>
                    </div>
                    <DialogFooter class="gap-2">
                        <DialogClose as-child>
                            <Button type="button" variant="ghost" class="text-zinc-400">
                                Cancel
                            </Button>
                        </DialogClose>
                        <Button
                            type="submit"
                            :disabled="!editProjectForm.name || editProjectForm.processing"
                            class="bg-indigo-600 hover:bg-indigo-500"
                        >
                            Save Changes
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
