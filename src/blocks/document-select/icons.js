/**
 * WordPress dependencies
 */
const { G, SVG, Path } = wp.components;

export const icon = (
	<SVG xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
		<Path fill="none" d="M0 0h24v24H0V0z" />
		<G>
			<Path d="M8 16h8v2H8zM8 12h8v2H8z" />
			<Path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm4 18H6V4h7v5h5v11z" />
		</G>
	</SVG>
);
