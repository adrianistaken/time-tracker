import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    auth: Auth;
    sidebarOpen: boolean;
    [key: string]: unknown;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
}

export type BreadcrumbItemType = BreadcrumbItem;

// Time Tracker Types
export interface Project {
    id: number;
    name: string;
    color: string;
    total_seconds?: number;
}

export interface Session {
    id: number;
    started_at: string;
    ended_at?: string;
    duration_seconds?: number;
    formatted_duration?: string;
    note?: string | null;
    project: Project;
}

export interface ActiveSession {
    id: number;
    started_at: string;
    project: Project;
}

export interface DailyTrendItem {
    date: string;
    full_date: string;
    seconds: number;
    hours: number;
}

export interface ProjectBreakdownItem {
    project_id: number;
    name: string;
    color: string;
    total_seconds: number;
}
